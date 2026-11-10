-- Apply after schema.sql and functions.sql.

create policy "Profiles are visible to their owner and administrators"
on public.profiles for select to authenticated
using (id = (select auth.uid()) or public.is_school_admin());
create policy "Users update their own basic profile details"
on public.profiles for update to authenticated
using (id = (select auth.uid()) or public.is_school_admin())
with check (id = (select auth.uid()) or public.is_school_admin());

create policy "Academic years are visible to authenticated users"
on public.academic_years for select to authenticated using (true);
create policy "Administrators manage academic years"
on public.academic_years for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Teachers see their own records or administrators see all"
on public.teachers for select to authenticated
using (profile_id = (select auth.uid()) or public.is_school_admin());
create policy "Administrators manage teachers"
on public.teachers for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Subjects are visible to assigned teachers and administrators"
on public.subjects for select to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1 from public.teacher_assignments ta
        join public.teachers t on t.id = ta.teacher_id
        where ta.subject_id = public.subjects.id and t.profile_id = (select auth.uid())
    )
    or exists (
        select 1 from public.teacher_assignments ta
        join public.students s on s.class_id = ta.class_id and s.academic_year_id = ta.academic_year_id
        where ta.subject_id = public.subjects.id and public.can_access_student(s.id)
    )
);
create policy "Administrators manage subjects"
on public.subjects for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Users see classes they are authorized to access"
on public.classes for select to authenticated
using (public.can_access_class(id));
create policy "Administrators manage classes"
on public.classes for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Users see students they are authorized to access"
on public.students for select to authenticated
using (public.can_access_student(id));
create policy "Administrators manage students"
on public.students for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Guardians see their own linked guardian record"
on public.guardians for select to authenticated
using (profile_id = (select auth.uid()) or public.is_school_admin());
create policy "Administrators manage guardians"
on public.guardians for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Students guardians and administrators can see student links"
on public.student_guardians for select to authenticated
using (
    public.is_school_admin()
    or public.can_access_student(student_id)
    or exists (
        select 1 from public.guardians g
        where g.id = guardian_id and g.profile_id = (select auth.uid())
    )
);
create policy "Administrators manage student guardian links"
on public.student_guardians for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Users see authorized teacher assignments"
on public.teacher_assignments for select to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1 from public.teachers t
        where t.id = teacher_id and t.profile_id = (select auth.uid())
    )
    or exists (
        select 1 from public.students s
        where s.class_id = public.teacher_assignments.class_id
          and s.academic_year_id = public.teacher_assignments.academic_year_id
          and public.can_access_student(s.id)
    )
);
create policy "Administrators manage teacher assignments"
on public.teacher_assignments for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Users see schedules for accessible classes"
on public.schedules for select to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1 from public.teacher_assignments ta
        where ta.id = public.schedules.teacher_assignment_id
          and public.can_access_class(ta.class_id)
    )
);
create policy "Administrators manage schedules"
on public.schedules for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Users see authorized attendance"
on public.attendance for select to authenticated
using (public.can_access_student(student_id));
create policy "Administrators and teachers record authorized attendance"
on public.attendance for insert to authenticated
with check (
    recorded_by = (select auth.uid())
    and (
        public.is_school_admin()
        or (
            public.can_teach_class(class_id, null, academic_year_id)
        )
    )
    and exists (
        select 1 from public.students s
        where s.id = public.attendance.student_id
          and s.class_id = public.attendance.class_id
          and s.academic_year_id = public.attendance.academic_year_id
    )
);
create policy "Teachers update assigned-class attendance"
on public.attendance for update to authenticated
using (public.is_school_admin() or public.can_teach_class(class_id, null, academic_year_id))
with check (
    (public.is_school_admin() or public.can_teach_class(class_id, null, academic_year_id))
    and exists (
        select 1 from public.students s
        where s.id = public.attendance.student_id
          and s.class_id = public.attendance.class_id
          and s.academic_year_id = public.attendance.academic_year_id
    )
);
create policy "Administrators delete attendance"
on public.attendance for delete to authenticated
using (public.is_school_admin());

create policy "Users see exams for accessible classes"
on public.exams for select to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1 from public.teacher_assignments ta
        where ta.id = public.exams.teacher_assignment_id
          and public.can_access_class(ta.class_id)
    )
);
create policy "Administrators and teachers create authorized exams"
on public.exams for insert to authenticated
with check (
    created_by = (select auth.uid())
    and (
        public.is_school_admin()
        or exists (
            select 1 from public.teacher_assignments ta
            where ta.id = public.exams.teacher_assignment_id
              and public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
        )
    )
);
create policy "Teachers update exams for assigned classes"
on public.exams for update to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1 from public.teacher_assignments ta
        where ta.id = public.exams.teacher_assignment_id
          and public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
    )
)
with check (
    public.is_school_admin()
    or exists (
        select 1 from public.teacher_assignments ta
        where ta.id = public.exams.teacher_assignment_id
          and public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
    )
);
create policy "Administrators delete exams"
on public.exams for delete to authenticated
using (public.is_school_admin());

create policy "Users see grades for accessible students"
on public.grades for select to authenticated
using (public.can_access_student(public.grades.student_id));
create policy "Administrators and teachers enter authorized grades"
on public.grades for insert to authenticated
with check (
    entered_by = (select auth.uid())
    and exists (
        select 1
        from public.exams e
        join public.teacher_assignments ta on ta.id = e.teacher_assignment_id
        join public.students s on s.id = public.grades.student_id
        where e.id = public.grades.exam_id
          and s.class_id = ta.class_id
          and s.academic_year_id = ta.academic_year_id
          and public.grades.points <= e.max_points
          and (
              public.is_school_admin()
              or public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
          )
    )
);
create policy "Teachers update grades for assigned exam students"
on public.grades for update to authenticated
using (
    public.is_school_admin()
    or exists (
        select 1
        from public.exams e
        join public.teacher_assignments ta on ta.id = e.teacher_assignment_id
        where e.id = public.grades.exam_id
          and public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
    )
)
with check (
    exists (
        select 1
        from public.exams e
        join public.teacher_assignments ta on ta.id = e.teacher_assignment_id
        join public.students s on s.id = public.grades.student_id
        where e.id = public.grades.exam_id
          and s.class_id = ta.class_id
          and s.academic_year_id = ta.academic_year_id
          and public.grades.points <= e.max_points
          and (
              public.is_school_admin()
              or public.can_teach_class(ta.class_id, ta.subject_id, ta.academic_year_id)
          )
    )
);
create policy "Administrators delete grades"
on public.grades for delete to authenticated using (public.is_school_admin());

create policy "Users see published announcements for their audience"
on public.announcements for select to authenticated
using (
    public.is_school_admin()
    or (
        published_at is not null
        and public.current_profile_role() = any (audience_roles)
        and (class_id is null or public.can_access_class(class_id))
    )
    or (created_by = (select auth.uid()) and published_at is null)
);
create policy "Administrators and assigned teachers create announcements"
on public.announcements for insert to authenticated
with check (
    created_by = (select auth.uid())
    and (
        public.is_school_admin()
        or (
            published_at is null
            and class_id is not null
            and public.can_teach_class(class_id)
        )
    )
);
create policy "Administrators manage announcements"
on public.announcements for update to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());
create policy "Administrators delete announcements"
on public.announcements for delete to authenticated
using (public.is_school_admin());

create policy "Recipients see their notifications"
on public.notifications for select to authenticated
using (recipient_profile_id = (select auth.uid()) or public.is_school_admin());
create policy "Recipients mark their notifications read"
on public.notifications for update to authenticated
using (recipient_profile_id = (select auth.uid()) or public.is_school_admin())
with check (recipient_profile_id = (select auth.uid()) or public.is_school_admin());

create policy "Users see authorized behavior records"
on public.behavior_records for select to authenticated
using (public.can_access_student(student_id));
create policy "Teachers record behavior for assigned students"
on public.behavior_records for insert to authenticated
with check (
    recorded_by = (select auth.uid())
    and (
        public.is_school_admin()
        or exists (
            select 1 from public.students s
            where s.id = public.behavior_records.student_id
              and public.can_teach_class(s.class_id, null, s.academic_year_id)
        )
    )
);
create policy "Administrators update behavior records"
on public.behavior_records for update to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());
create policy "Administrators delete behavior records"
on public.behavior_records for delete to authenticated using (public.is_school_admin());

create policy "Requesters and administrators see leave requests"
on public.leave_requests for select to authenticated
using (
    requester_profile_id = (select auth.uid())
    or public.is_school_admin()
    or (student_id is not null and public.can_access_student(student_id))
);
create policy "Users request leave for themselves or linked students"
on public.leave_requests for insert to authenticated
with check (
    requester_profile_id = (select auth.uid())
    and (
        (student_id is null and public.current_profile_role() in ('teacher', 'student'))
        or (student_id is not null and public.can_access_student(student_id))
    )
);
create policy "Administrators review leave requests"
on public.leave_requests for update to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());
create policy "Administrators delete leave requests"
on public.leave_requests for delete to authenticated using (public.is_school_admin());

create policy "Users see documents for authorized students"
on public.student_documents for select to authenticated
using (public.can_access_student(student_id));
create policy "Authorized users register documents for a student"
on public.student_documents for insert to authenticated
with check (
    uploaded_by = (select auth.uid())
    and public.can_access_student(student_id)
    and storage_path ~ (
        '^students/' || student_id::text || '/[^/]+$'
    )
);
create policy "Administrators delete student document records"
on public.student_documents for delete to authenticated
using (public.is_school_admin());

create policy "Authenticated users read school settings"
on public.school_settings for select to authenticated
using (
    public.is_school_admin()
    or key in ('school_name', 'school_name_en', 'school_logo_path', 'school_icon_path')
);
create policy "Administrators manage school settings"
on public.school_settings for all to authenticated
using (public.is_school_admin()) with check (public.is_school_admin());

create policy "Administrators read activity logs"
on public.activity_logs for select to authenticated
using (public.is_school_admin());
create policy "Administrators read backup logs"
on public.backup_logs for select to authenticated
using (public.is_school_admin());

create policy "Authorized users read student documents"
on storage.objects for select to authenticated
using (
    bucket_id = 'student-documents'
    and public.can_access_student_path(name)
);
create policy "Authorized users upload student documents"
on storage.objects for insert to authenticated
with check (
    bucket_id = 'student-documents'
    and public.can_access_student_path(name)
);
create policy "Administrators update student documents"
on storage.objects for update to authenticated
using (bucket_id = 'student-documents' and public.is_school_admin())
with check (bucket_id = 'student-documents' and public.is_school_admin());
create policy "Administrators delete student documents"
on storage.objects for delete to authenticated
using (bucket_id = 'student-documents' and public.is_school_admin());

-- Prevent client users from changing identity and authorization columns.
revoke update on public.profiles from authenticated;
grant update (display_name, phone_e164) on public.profiles to authenticated;

-- User-generated notification and audit rows are trigger/service-owned.
revoke insert, update, delete on public.notifications from authenticated;
grant update (read_at) on public.notifications to authenticated;
revoke insert, update, delete on public.activity_logs, public.backup_logs from authenticated;
