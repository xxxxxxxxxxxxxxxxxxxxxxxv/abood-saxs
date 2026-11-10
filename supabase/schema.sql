-- Fresh Supabase project schema for Martyr Hazem Makawy School.
-- This schema is an additive migration foundation; it does not migrate or
-- replace the RosarioSIS schema or existing school data.

create table if not exists public.profiles (
    id uuid primary key references auth.users (id) on delete cascade,
    role text not null check (role in ('system_admin', 'school_admin', 'teacher', 'student', 'guardian')),
    display_name text not null check (length(trim(display_name)) between 1 and 160),
    phone_e164 text unique check (phone_e164 is null or phone_e164 ~ '^\+[1-9][0-9]{7,14}$'),
    is_active boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.academic_years (
    id uuid primary key default gen_random_uuid(),
    name text not null unique,
    starts_on date not null,
    ends_on date not null,
    is_current boolean not null default false,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    constraint academic_years_date_range check (ends_on >= starts_on)
);

create table if not exists public.teachers (
    id uuid primary key default gen_random_uuid(),
    profile_id uuid unique references public.profiles (id) on delete set null,
    employee_number text not null unique,
    first_name text not null,
    last_name text not null,
    status text not null default 'active' check (status in ('active', 'inactive', 'archived')),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.subjects (
    id uuid primary key default gen_random_uuid(),
    code text not null unique,
    name text not null,
    description text,
    is_active boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.classes (
    id uuid primary key default gen_random_uuid(),
    academic_year_id uuid not null references public.academic_years (id) on delete restrict,
    name text not null,
    grade_level text not null,
    homeroom_teacher_id uuid references public.teachers (id) on delete set null,
    capacity integer check (capacity is null or capacity > 0),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (academic_year_id, name),
    unique (id, academic_year_id)
);

create table if not exists public.students (
    id uuid primary key default gen_random_uuid(),
    profile_id uuid unique references public.profiles (id) on delete set null,
    class_id uuid,
    academic_year_id uuid not null references public.academic_years (id) on delete restrict,
    student_number text not null unique,
    first_name text not null,
    middle_name text,
    last_name text not null,
    date_of_birth date,
    phone_e164 text check (phone_e164 is null or phone_e164 ~ '^\+[1-9][0-9]{7,14}$'),
    status text not null default 'active' check (status in ('active', 'inactive', 'archived')),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    foreign key (class_id, academic_year_id)
        references public.classes (id, academic_year_id) on delete restrict
);

create table if not exists public.guardians (
    id uuid primary key default gen_random_uuid(),
    profile_id uuid unique references public.profiles (id) on delete set null,
    relationship text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.student_guardians (
    student_id uuid not null references public.students (id) on delete cascade,
    guardian_id uuid not null references public.guardians (id) on delete cascade,
    is_primary boolean not null default false,
    created_at timestamptz not null default now(),
    primary key (student_id, guardian_id)
);

create table if not exists public.teacher_assignments (
    id uuid primary key default gen_random_uuid(),
    teacher_id uuid not null references public.teachers (id) on delete restrict,
    class_id uuid not null,
    subject_id uuid not null references public.subjects (id) on delete restrict,
    academic_year_id uuid not null references public.academic_years (id) on delete restrict,
    created_at timestamptz not null default now(),
    unique (teacher_id, class_id, subject_id, academic_year_id),
    foreign key (class_id, academic_year_id)
        references public.classes (id, academic_year_id) on delete restrict
);

create table if not exists public.schedules (
    id uuid primary key default gen_random_uuid(),
    teacher_assignment_id uuid not null references public.teacher_assignments (id) on delete cascade,
    weekday smallint not null check (weekday between 0 and 6),
    starts_at time not null,
    ends_at time not null,
    room text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    check (ends_at > starts_at)
);

create table if not exists public.attendance (
    id uuid primary key default gen_random_uuid(),
    student_id uuid not null references public.students (id) on delete restrict,
    class_id uuid not null references public.classes (id) on delete restrict,
    academic_year_id uuid not null references public.academic_years (id) on delete restrict,
    recorded_by uuid not null references public.profiles (id) on delete restrict,
    attendance_date date not null,
    period_number smallint not null default 0 check (period_number >= 0),
    status text not null check (status in ('present', 'absent', 'late', 'excused')),
    note text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (student_id, attendance_date, period_number)
);

create table if not exists public.exams (
    id uuid primary key default gen_random_uuid(),
    teacher_assignment_id uuid not null references public.teacher_assignments (id) on delete restrict,
    title text not null,
    exam_date date not null,
    max_points numeric(8, 2) not null check (max_points > 0),
    created_by uuid not null references public.profiles (id) on delete restrict,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.grades (
    id uuid primary key default gen_random_uuid(),
    exam_id uuid not null references public.exams (id) on delete restrict,
    student_id uuid not null references public.students (id) on delete restrict,
    points numeric(8, 2) not null check (points >= 0),
    feedback text,
    entered_by uuid not null references public.profiles (id) on delete restrict,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (exam_id, student_id)
);

create table if not exists public.announcements (
    id uuid primary key default gen_random_uuid(),
    title text not null check (length(trim(title)) between 1 and 200),
    body text not null check (length(trim(body)) > 0),
    audience_roles text[] not null default array['student', 'guardian']::text[],
    class_id uuid references public.classes (id) on delete cascade,
    published_at timestamptz,
    created_by uuid not null references public.profiles (id) on delete restrict,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    check (audience_roles <@ array['system_admin', 'school_admin', 'teacher', 'student', 'guardian']::text[])
);

create table if not exists public.notifications (
    id uuid primary key default gen_random_uuid(),
    recipient_profile_id uuid not null references public.profiles (id) on delete cascade,
    title text not null,
    body text not null,
    notification_type text not null check (notification_type in ('announcement', 'attendance', 'behavior', 'system')),
    related_record_id uuid,
    read_at timestamptz,
    created_at timestamptz not null default now()
);

create table if not exists public.behavior_records (
    id uuid primary key default gen_random_uuid(),
    student_id uuid not null references public.students (id) on delete restrict,
    recorded_by uuid not null references public.profiles (id) on delete restrict,
    occurred_at timestamptz not null default now(),
    category text not null,
    details text not null,
    action_taken text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.leave_requests (
    id uuid primary key default gen_random_uuid(),
    requester_profile_id uuid not null references public.profiles (id) on delete restrict,
    student_id uuid references public.students (id) on delete restrict,
    starts_at timestamptz not null,
    ends_at timestamptz not null,
    reason text not null,
    status text not null default 'pending' check (status in ('pending', 'approved', 'rejected', 'cancelled')),
    reviewed_by uuid references public.profiles (id) on delete set null,
    reviewed_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    check (ends_at >= starts_at)
);

create table if not exists public.student_documents (
    id uuid primary key default gen_random_uuid(),
    student_id uuid not null references public.students (id) on delete restrict,
    uploaded_by uuid not null references public.profiles (id) on delete restrict,
    storage_path text not null unique,
    file_name text not null,
    content_type text not null,
    size_bytes bigint not null check (size_bytes > 0),
    created_at timestamptz not null default now()
);

create table if not exists public.school_settings (
    key text primary key,
    value jsonb not null,
    updated_by uuid references public.profiles (id) on delete set null,
    updated_at timestamptz not null default now()
);

create table if not exists public.activity_logs (
    id bigint generated always as identity primary key,
    actor_profile_id uuid references public.profiles (id) on delete set null,
    action text not null check (action in ('INSERT', 'UPDATE', 'DELETE')),
    entity text not null,
    entity_id text,
    created_at timestamptz not null default now()
);

create table if not exists public.backup_logs (
    id uuid primary key default gen_random_uuid(),
    created_by uuid references public.profiles (id) on delete set null,
    backup_type text not null check (backup_type in ('manual', 'scheduled', 'provider')),
    status text not null check (status in ('started', 'completed', 'failed')),
    details jsonb not null default '{}'::jsonb,
    created_at timestamptz not null default now()
);

create unique index if not exists academic_years_one_current_idx
    on public.academic_years (is_current) where is_current;
create index if not exists students_class_year_idx on public.students (class_id, academic_year_id);
create index if not exists students_profile_idx on public.students (profile_id) where profile_id is not null;
create index if not exists students_phone_idx on public.students (phone_e164) where phone_e164 is not null;
create index if not exists student_guardians_guardian_idx on public.student_guardians (guardian_id, student_id);
create index if not exists teacher_assignments_class_idx on public.teacher_assignments (class_id, academic_year_id);
create index if not exists teacher_assignments_teacher_idx on public.teacher_assignments (teacher_id, academic_year_id);
create index if not exists schedules_assignment_weekday_idx on public.schedules (teacher_assignment_id, weekday, starts_at);
create index if not exists attendance_student_date_idx on public.attendance (student_id, attendance_date desc);
create index if not exists attendance_class_date_idx on public.attendance (class_id, attendance_date desc);
create index if not exists exams_assignment_date_idx on public.exams (teacher_assignment_id, exam_date);
create index if not exists grades_student_idx on public.grades (student_id, exam_id);
create index if not exists announcements_published_idx on public.announcements (published_at desc) where published_at is not null;
create index if not exists notifications_recipient_unread_idx on public.notifications (recipient_profile_id, created_at desc) where read_at is null;
create index if not exists behavior_records_student_time_idx on public.behavior_records (student_id, occurred_at desc);
create index if not exists leave_requests_requester_status_idx on public.leave_requests (requester_profile_id, status);
create index if not exists student_documents_student_idx on public.student_documents (student_id, created_at desc);
create index if not exists activity_logs_entity_time_idx on public.activity_logs (entity, entity_id, created_at desc);
create index if not exists backup_logs_created_idx on public.backup_logs (created_at desc);

alter table public.profiles enable row level security;
alter table public.academic_years enable row level security;
alter table public.teachers enable row level security;
alter table public.subjects enable row level security;
alter table public.classes enable row level security;
alter table public.students enable row level security;
alter table public.guardians enable row level security;
alter table public.student_guardians enable row level security;
alter table public.teacher_assignments enable row level security;
alter table public.schedules enable row level security;
alter table public.attendance enable row level security;
alter table public.exams enable row level security;
alter table public.grades enable row level security;
alter table public.announcements enable row level security;
alter table public.notifications enable row level security;
alter table public.behavior_records enable row level security;
alter table public.leave_requests enable row level security;
alter table public.student_documents enable row level security;
alter table public.school_settings enable row level security;
alter table public.activity_logs enable row level security;
alter table public.backup_logs enable row level security;

-- Private bucket; all uploaded object access is controlled by storage policies.
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values (
    'student-documents',
    'student-documents',
    false,
    10485760,
    array['image/jpeg', 'image/png', 'image/webp', 'application/pdf']
)
on conflict (id) do update
set public = false,
    file_size_limit = excluded.file_size_limit,
    allowed_mime_types = excluded.allowed_mime_types;

-- Limit grants to these application tables; do not alter privileges on legacy tables.
revoke all on
    public.profiles,
    public.academic_years,
    public.teachers,
    public.subjects,
    public.classes,
    public.students,
    public.guardians,
    public.student_guardians,
    public.teacher_assignments,
    public.schedules,
    public.attendance,
    public.exams,
    public.grades,
    public.announcements,
    public.notifications,
    public.behavior_records,
    public.leave_requests,
    public.student_documents,
    public.school_settings,
    public.activity_logs,
    public.backup_logs
from public, anon;

grant select on public.profiles to authenticated;
grant select, insert, delete on
    public.academic_years,
    public.teachers,
    public.subjects,
    public.classes,
    public.students,
    public.guardians,
    public.student_guardians,
    public.teacher_assignments,
    public.schedules,
    public.attendance,
    public.exams,
    public.grades,
    public.announcements,
    public.notifications,
    public.behavior_records,
    public.leave_requests,
    public.student_documents,
    public.school_settings
to authenticated;
grant select on public.activity_logs, public.backup_logs to authenticated;
grant update (name, starts_on, ends_on, is_current) on public.academic_years to authenticated;
grant update (profile_id, employee_number, first_name, last_name, status)
    on public.teachers to authenticated;
grant update (code, name, description, is_active) on public.subjects to authenticated;
grant update (academic_year_id, name, grade_level, homeroom_teacher_id, capacity)
    on public.classes to authenticated;
grant update (
    profile_id, class_id, academic_year_id, student_number,
    first_name, middle_name, last_name, date_of_birth, phone_e164, status
) on public.students to authenticated;
grant update (profile_id, relationship) on public.guardians to authenticated;
grant update (teacher_assignment_id, weekday, starts_at, ends_at, room)
    on public.schedules to authenticated;
grant update (status, note) on public.attendance to authenticated;
grant update (title, exam_date, max_points) on public.exams to authenticated;
grant update (points, feedback) on public.grades to authenticated;
grant update (title, body, audience_roles, class_id, published_at)
    on public.announcements to authenticated;
grant update (category, details, action_taken, occurred_at)
    on public.behavior_records to authenticated;
grant update (status, reviewed_by, reviewed_at) on public.leave_requests to authenticated;
grant update (key, value, updated_by) on public.school_settings to authenticated;
grant update (display_name, phone_e164) on public.profiles to authenticated;
revoke insert, delete on public.profiles from authenticated;
revoke insert, update, delete on public.activity_logs, public.backup_logs from authenticated;
