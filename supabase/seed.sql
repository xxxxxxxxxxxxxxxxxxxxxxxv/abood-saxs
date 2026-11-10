-- Safe, non-personal initial settings only. No sample student or staff accounts.
insert into public.school_settings (key, value)
values
    ('school_name', to_jsonb('مدرسة الشهيد حازم مكاوي'::text)),
    ('school_name_en', to_jsonb('Martyr Hazem Makawy School'::text))
on conflict (key) do nothing;
