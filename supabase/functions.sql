-- Apply after schema.sql and before policies.sql.

create or replace function public.set_updated_at()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
    new.updated_at := now();
    return new;
end;
$$;

create or replace function public.current_profile_role()
returns text
language sql
stable
security definer
set search_path = ''
as $$
    select p.role
    from public.profiles p
    where p.id = (select auth.uid())
      and p.is_active
$$;

create or replace function public.is_school_admin()
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
    select coalesce(
        public.current_profile_role() in ('system_admin', 'school_admin'),
        false
    )
$$;

create or replace function public.can_teach_class(
    target_class_id uuid,
    target_subject_id uuid default null,
    target_academic_year_id uuid default null
)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
    select public.is_school_admin()
        or exists (
            select 1
            from public.teacher_assignments ta
            join public.teachers t on t.id = ta.teacher_id
            join public.profiles p on p.id = t.profile_id
            where p.id = (select auth.uid())
              and p.role = 'teacher'
              and p.is_active
              and t.status = 'active'
              and ta.class_id = target_class_id
              and (target_subject_id is null or ta.subject_id = target_subject_id)
              and (target_academic_year_id is null or ta.academic_year_id = target_academic_year_id)
        )
$$;

create or replace function public.can_access_student(target_student_id uuid)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
    select public.is_school_admin()
        or exists (
            select 1
            from public.students s
            where s.id = target_student_id
              and s.profile_id = (select auth.uid())
              and exists (
                  select 1 from public.profiles p
                  where p.id = (select auth.uid())
                    and p.role = 'student'
                    and p.is_active
              )
        )
        or exists (
            select 1
            from public.student_guardians sg
            join public.guardians g on g.id = sg.guardian_id
            where sg.student_id = target_student_id
              and g.profile_id = (select auth.uid())
              and exists (
                  select 1 from public.profiles p
                  where p.id = (select auth.uid())
                    and p.role = 'guardian'
                    and p.is_active
              )
        )
        or exists (
            select 1
            from public.students s
            join public.teacher_assignments ta
              on ta.class_id = s.class_id
             and ta.academic_year_id = s.academic_year_id
            join public.teachers t on t.id = ta.teacher_id
            where s.id = target_student_id
              and t.profile_id = (select auth.uid())
              and t.status = 'active'
              and exists (
                  select 1 from public.profiles p
                  where p.id = (select auth.uid())
                    and p.role = 'teacher'
                    and p.is_active
              )
        )
$$;

create or replace function public.can_access_class(target_class_id uuid)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
    select public.is_school_admin()
        or public.can_teach_class(target_class_id)
        or exists (
            select 1
            from public.students s
            where s.class_id = target_class_id
              and public.can_access_student(s.id)
        )
$$;

create or replace function public.can_access_student_path(object_path text)
returns boolean
language plpgsql
stable
security definer
set search_path = ''
as $$
declare
    student_id_text text;
begin
    if object_path !~ '^students/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/[^/]+$' then
        return false;
    end if;

    student_id_text := substring(object_path from 10 for 36);

    return public.can_access_student(student_id_text::uuid);
end;
$$;

create or replace function public.log_activity_change()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
    changed_row jsonb;
begin
    changed_row := case when tg_op = 'DELETE' then to_jsonb(old) else to_jsonb(new) end;

    insert into public.activity_logs (actor_profile_id, action, entity, entity_id)
    values (
        (select auth.uid()),
        tg_op,
        tg_table_name,
        changed_row ->> 'id'
    );

    if tg_op = 'DELETE' then
        return old;
    end if;

    return new;
end;
$$;

create or replace function public.notify_student_guardians()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
    notification_title text;
    notification_body text;
    target_student_id uuid;
begin
    if tg_table_name = 'attendance' then
        if new.status <> 'absent'
           or (tg_op = 'UPDATE' and old.status = 'absent') then
            return new;
        end if;

        target_student_id := new.student_id;
        notification_title := 'تسجيل غياب جديد';
        notification_body := 'تم تسجيل غياب بتاريخ ' || new.attendance_date::text || '.';
    else
        target_student_id := new.student_id;
        notification_title := 'سجل سلوك الطالب';
        notification_body := new.category || ': ' || left(new.details, 300);
    end if;

    insert into public.notifications (
        recipient_profile_id,
        title,
        body,
        notification_type,
        related_record_id
    )
    select distinct g.profile_id,
        notification_title,
        notification_body,
        case when tg_table_name = 'attendance' then 'attendance' else 'behavior' end,
        new.id
    from public.student_guardians sg
    join public.guardians g on g.id = sg.guardian_id
    join public.profiles p on p.id = g.profile_id and p.is_active
    where sg.student_id = target_student_id
      and g.profile_id is not null;

    return new;
end;
$$;

create or replace function public.notify_announcement_recipients()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
    if new.published_at is null
       or (tg_op = 'UPDATE' and old.published_at is not null) then
        return new;
    end if;

    insert into public.notifications (
        recipient_profile_id,
        title,
        body,
        notification_type,
        related_record_id
    )
    select p.id, new.title, new.body, 'announcement', new.id
    from public.profiles p
    where p.is_active
      and p.role = any (new.audience_roles)
      and (
          new.class_id is null
          or p.role in ('system_admin', 'school_admin')
          or exists (
              select 1
              from public.students s
              where s.class_id = new.class_id
                and s.profile_id = p.id
          )
          or exists (
              select 1
              from public.student_guardians sg
              join public.guardians g on g.id = sg.guardian_id
              join public.students s on s.id = sg.student_id
              where s.class_id = new.class_id
                and g.profile_id = p.id
          )
          or exists (
              select 1
              from public.teacher_assignments ta
              join public.teachers t on t.id = ta.teacher_id
              where ta.class_id = new.class_id
                and t.profile_id = p.id
          )
      );

    return new;
end;
$$;

create or replace function public.set_profile_access(
    target_profile_id uuid,
    target_role text,
    target_is_active boolean
)
returns void
language plpgsql
security definer
set search_path = ''
as $$
begin
    if not public.is_school_admin() then
        raise exception 'Administrator access is required';
    end if;

    if target_role not in ('system_admin', 'school_admin', 'teacher', 'student', 'guardian') then
        raise exception 'Invalid profile role';
    end if;

    update public.profiles
    set role = target_role,
        is_active = target_is_active,
        updated_at = now()
    where id = target_profile_id;

    if not found then
        raise exception 'Profile not found';
    end if;
end;
$$;

revoke all on function public.set_updated_at() from public, anon, authenticated;
revoke all on function public.current_profile_role() from public, anon;
revoke all on function public.is_school_admin() from public, anon;
revoke all on function public.can_teach_class(uuid, uuid, uuid) from public, anon;
revoke all on function public.can_access_student(uuid) from public, anon;
revoke all on function public.can_access_class(uuid) from public, anon;
revoke all on function public.can_access_student_path(text) from public, anon;
revoke all on function public.log_activity_change() from public, anon, authenticated;
revoke all on function public.notify_student_guardians() from public, anon, authenticated;
revoke all on function public.notify_announcement_recipients() from public, anon, authenticated;
revoke all on function public.set_profile_access(uuid, text, boolean) from public, anon;

grant execute on function public.current_profile_role() to authenticated;
grant execute on function public.is_school_admin() to authenticated;
grant execute on function public.can_teach_class(uuid, uuid, uuid) to authenticated;
grant execute on function public.can_access_student(uuid) to authenticated;
grant execute on function public.can_access_class(uuid) to authenticated;
grant execute on function public.can_access_student_path(text) to authenticated;
grant execute on function public.set_profile_access(uuid, text, boolean) to authenticated;

drop trigger if exists profiles_set_updated_at on public.profiles;
create trigger profiles_set_updated_at before update on public.profiles
for each row execute function public.set_updated_at();
drop trigger if exists academic_years_set_updated_at on public.academic_years;
create trigger academic_years_set_updated_at before update on public.academic_years
for each row execute function public.set_updated_at();
drop trigger if exists teachers_set_updated_at on public.teachers;
create trigger teachers_set_updated_at before update on public.teachers
for each row execute function public.set_updated_at();
drop trigger if exists subjects_set_updated_at on public.subjects;
create trigger subjects_set_updated_at before update on public.subjects
for each row execute function public.set_updated_at();
drop trigger if exists classes_set_updated_at on public.classes;
create trigger classes_set_updated_at before update on public.classes
for each row execute function public.set_updated_at();
drop trigger if exists students_set_updated_at on public.students;
create trigger students_set_updated_at before update on public.students
for each row execute function public.set_updated_at();
drop trigger if exists guardians_set_updated_at on public.guardians;
create trigger guardians_set_updated_at before update on public.guardians
for each row execute function public.set_updated_at();
drop trigger if exists schedules_set_updated_at on public.schedules;
create trigger schedules_set_updated_at before update on public.schedules
for each row execute function public.set_updated_at();
drop trigger if exists attendance_set_updated_at on public.attendance;
create trigger attendance_set_updated_at before update on public.attendance
for each row execute function public.set_updated_at();
drop trigger if exists exams_set_updated_at on public.exams;
create trigger exams_set_updated_at before update on public.exams
for each row execute function public.set_updated_at();
drop trigger if exists grades_set_updated_at on public.grades;
create trigger grades_set_updated_at before update on public.grades
for each row execute function public.set_updated_at();
drop trigger if exists announcements_set_updated_at on public.announcements;
create trigger announcements_set_updated_at before update on public.announcements
for each row execute function public.set_updated_at();
drop trigger if exists behavior_records_set_updated_at on public.behavior_records;
create trigger behavior_records_set_updated_at before update on public.behavior_records
for each row execute function public.set_updated_at();
drop trigger if exists leave_requests_set_updated_at on public.leave_requests;
create trigger leave_requests_set_updated_at before update on public.leave_requests
for each row execute function public.set_updated_at();
drop trigger if exists school_settings_set_updated_at on public.school_settings;
create trigger school_settings_set_updated_at before update on public.school_settings
for each row execute function public.set_updated_at();

drop trigger if exists students_activity_log on public.students;
create trigger students_activity_log after insert or update or delete on public.students
for each row execute function public.log_activity_change();
drop trigger if exists teachers_activity_log on public.teachers;
create trigger teachers_activity_log after insert or update or delete on public.teachers
for each row execute function public.log_activity_change();
drop trigger if exists classes_activity_log on public.classes;
create trigger classes_activity_log after insert or update or delete on public.classes
for each row execute function public.log_activity_change();
drop trigger if exists attendance_activity_log on public.attendance;
create trigger attendance_activity_log after insert or update or delete on public.attendance
for each row execute function public.log_activity_change();
drop trigger if exists grades_activity_log on public.grades;
create trigger grades_activity_log after insert or update or delete on public.grades
for each row execute function public.log_activity_change();
drop trigger if exists announcements_activity_log on public.announcements;
create trigger announcements_activity_log after insert or update or delete on public.announcements
for each row execute function public.log_activity_change();
drop trigger if exists behavior_records_activity_log on public.behavior_records;
create trigger behavior_records_activity_log after insert or update or delete on public.behavior_records
for each row execute function public.log_activity_change();
drop trigger if exists leave_requests_activity_log on public.leave_requests;
create trigger leave_requests_activity_log after insert or update or delete on public.leave_requests
for each row execute function public.log_activity_change();

drop trigger if exists attendance_guardian_notification on public.attendance;
create trigger attendance_guardian_notification after insert or update on public.attendance
for each row execute function public.notify_student_guardians();
drop trigger if exists behavior_guardian_notification on public.behavior_records;
create trigger behavior_guardian_notification after insert on public.behavior_records
for each row execute function public.notify_student_guardians();
drop trigger if exists announcement_notifications on public.announcements;
create trigger announcement_notifications after insert or update on public.announcements
for each row execute function public.notify_announcement_recipients();
