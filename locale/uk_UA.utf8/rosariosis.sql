--
-- PostgreSQL & MySQL data update
--
-- Translates database fields to Ukrainian
--
-- Note: Uncheck "Paginate results" when importing with phpPgAdmin
--

--
-- Data for Name: schools; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE schools
SET title='Школа (UA)', address='Вул. Зелена, б. 15', city='Іванівськ', state=NULL, zipcode='10000', principal='Іванченко І. І.', www_address='www.rosariosis.org', reporting_gp_scale=10
WHERE id=1;


--
-- Data for Name: attendance_calendars; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE attendance_calendars
SET title='Основний'
WHERE calendar_id=1;


--
-- Data for Name: config; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE config
SET config_value='Rosario Student Information System|uk_UA.utf8:Навчальна інформаційна система Rosario'
WHERE title='TITLE';

UPDATE config
SET config_value='₴'
WHERE title='CURRENCY';

UPDATE config
SET config_value=','
WHERE title='DECIMAL_SEPARATOR';

UPDATE config
SET config_value='&nbsp;'
WHERE title='THOUSANDS_SEPARATOR';


--
-- Data for Name: student_enrollment_codes; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE student_enrollment_codes
SET title='Випуск', short_name='ВИП'
WHERE id=1;

UPDATE student_enrollment_codes
SET title='Виключення', short_name='ВИКЛ'
WHERE id=2;

UPDATE student_enrollment_codes
SET title='Початок року', short_name='ПОЧРОКУ'
WHERE id=3;

UPDATE student_enrollment_codes
SET title='Інший район', short_name='ІНШРАЙОН'
WHERE id=4;

UPDATE student_enrollment_codes
SET title='Перенесення', short_name='TRAN'
WHERE id=5;

UPDATE student_enrollment_codes
SET title='Перенесення', short_name='MAN'
WHERE id=6;


--
-- Data for Name: report_card_grade_scales; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE report_card_grade_scales
SET title='Головне', gp_scale=10, gp_passing_value=5
WHERE id=1;


--
-- Data for Name: report_card_grades; Type: TABLE DATA; Schema: public; Owner: postgres
--


UPDATE report_card_grades
SET title='10.0', gpa_value=10.0, break_off=97.5, comment='Відмінно'
WHERE id=1;

UPDATE report_card_grades
SET title='9.5', gpa_value=9.5, break_off=92.5, comment='Відмінно'
WHERE id=2;

UPDATE report_card_grades
SET title='9.0', gpa_value=9.0, break_off=87.5, comment='Відмінно'
WHERE id=3;

UPDATE report_card_grades
SET title='8.5', gpa_value=8.5, break_off=82.5, comment='Відмінно'
WHERE id=4;

UPDATE report_card_grades
SET title='8.0', gpa_value=8.0, break_off=77.5, comment='Добре'
WHERE id=5;

UPDATE report_card_grades
SET title='7.5', gpa_value=7.5, break_off=72.5, comment='Добре'
WHERE id=6;

UPDATE report_card_grades
SET title='7.0', gpa_value=7.0, break_off=67.5, comment='Добре'
WHERE id=7;

UPDATE report_card_grades
SET title='6.5', gpa_value=6.5, break_off=62.5, comment='Задовільно'
WHERE id=8;

UPDATE report_card_grades
SET title='6.0', gpa_value=6.0, break_off=57.5, comment='Задовільно'
WHERE id=9;

UPDATE report_card_grades
SET title='5.5', gpa_value=5.5, break_off=52.5, comment='Незадовільно'
WHERE id=10;

UPDATE report_card_grades
SET title='5.0', gpa_value=5.0, break_off=47.5, comment='Незадовільно'
WHERE id=11;

UPDATE report_card_grades
SET title='4.5', gpa_value=4.5, break_off=42.5, comment='Слабко'
WHERE id=12;

UPDATE report_card_grades
SET title='4.0', gpa_value=4.0, break_off=37.5, comment='Слабко'
WHERE id=13;

UPDATE report_card_grades
SET title='3.5', gpa_value=3.5, break_off=32.5, comment='Слабко'
WHERE id=14;

UPDATE report_card_grades
SET title='3.0', gpa_value=3.0, break_off=27.5, comment='Слабко'
WHERE id=15;

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '2.5', 16, 2.5, 22.5, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '2.0', 17, 2.0, 17.5, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '1.5', 18, 1.5, 12.5, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '1.0', 19, 1.0, 7.5, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '0.5', 20, 0.5, 2.5, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '0.0', 21, 0.0, 0, 'Погано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, 'I', 22, 0.0, 0, 'Не здано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, 'Н/Д', 23, NULL, NULL, NULL, 1, NULL);


--
-- Data for Name: school_marking_periods; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE school_marking_periods
SET title='Увесь рік', short_name='Рік'
WHERE marking_period_id=1;

UPDATE school_marking_periods
SET title='1 семестр', short_name='1С'
WHERE marking_period_id=2;

UPDATE school_marking_periods
SET title='2 семестр', short_name='2С'
WHERE marking_period_id=3;

UPDATE school_marking_periods
SET title='1 чверть', short_name='1Ч'
WHERE marking_period_id=4;

UPDATE school_marking_periods
SET title='2 чверть', short_name='2Ч'
WHERE marking_period_id=5;

UPDATE school_marking_periods
SET title='3 чверть', short_name='3Ч'
WHERE marking_period_id=6;

UPDATE school_marking_periods
SET title='4 чверть', short_name='4Ч'
WHERE marking_period_id=7;


--
-- Data for Name: school_periods; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE school_periods
SET title='Увесь день', short_name='УВЕСЬДЕНЬ'
WHERE period_id=1;

UPDATE school_periods
SET title='Ранок', short_name='РАН'
WHERE period_id=2;

UPDATE school_periods
SET title='День', short_name='ДЕНЬ'
WHERE period_id=3;

UPDATE school_periods
SET title='1 урок', short_name='01'
WHERE period_id=4;

UPDATE school_periods
SET title='2 урок', short_name='02'
WHERE period_id=5;

UPDATE school_periods
SET title='3 урок', short_name='03'
WHERE period_id=6;

UPDATE school_periods
SET title='4 урок', short_name='04'
WHERE period_id=7;

UPDATE school_periods
SET title='5 урок', short_name='05'
WHERE period_id=8;

UPDATE school_periods
SET title='6 урок', short_name='06'
WHERE period_id=9;

UPDATE school_periods
SET title='7 урок', short_name='07'
WHERE period_id=10;

UPDATE school_periods
SET title='8 урок', short_name='08'
WHERE period_id=11;


--
-- Data for Name: templates; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE templates
SET template='<br /><br /><br />
<div style="text-align: center;"><span style="font-size: xx-large;"><strong>__SCHOOL_ID__</strong><br /></span><br /><span style="font-size: xx-large;">Нагороджується<br /><br /></span></div>
<div style="text-align: center;"><span style="font-size: xx-large;"><strong>__FIRST_NAME__ __LAST_NAME__</strong><br /><br /></span></div>
<div style="text-align: center;"><span style="font-size: xx-large;">за високу<br />успішність</span></div>'
WHERE modname='Grades/HonorRoll.php';

UPDATE templates
SET template='<div style="text-align: center;">__CLIPART__<br /><br /><strong><span style="font-size: xx-large;">__SCHOOL_ID__<br /></span></strong><br /><span style="font-size: xx-large;">Нагороджується<br /><br /></span></div>
<div style="text-align: center;"><strong><span style="font-size: xx-large;">__FIRST_NAME__ __LAST_NAME__<br /><br /></span></strong></div>
<div style="text-align: center;"><span style="font-size: xx-large;">за відмінне навчання з предмету<br />__SUBJECT__</span></div>'
WHERE modname='Grades/HonorRollSubject.php';

UPDATE templates
SET template='<h2 style="text-align: center;">Сертифікат з навчання</h2>
<p>Згідно інформації від директора та секретаріата:</p>
<p>Что __FIRST_NAME__ __LAST_NAME__ з ідентифікатором __SSECURITY__ проходить навчання в цьому закладі, у класі __GRADE_ID__ у навчальному році __YEAR__ й отримав(ла) оцінки.</p>
<p>Учень(ниця) переходить до класу __NEXT_GRADE_ID__.</p>
<p>__BLOCK2__</p>
<p>&nbsp;</p>
<table style="border-collapse: collapse; width: 100%;" border="0" cellpadding="10"><tbody><tr>
<td style="width: 50%; text-align: center;"><hr />
<p>Підпис</p>
<p>&nbsp;</p><hr />
<p>Ім''я</p></td>
<td style="width: 50%; text-align: center;"><hr />
<p>Підпис</p>
<p>&nbsp;</p><hr />
<p>Ім''я</p></td></tr></tbody></table>'
WHERE modname='Grades/Transcripts.php';

UPDATE templates
SET template='Шановний(на) __PARENT_NAME__,

Для вас створено батьківський обліковий засіб у школі __SCHOOL_ID__ для доступу до інформації про цю школу й учнів:
__ASSOCIATED_STUDENTS__

Ваші дані для входу:
Псевдонім: __USERNAME__
Пароль: __PASSWORD__

Посилання на сайт інформаційної системи школи та інструкції щодо доступу до неї доступні на сайті школи.__BLOCK2__Шановний(на) __PARENT_NAME__,

Наступні учні були прив''язані до вашого батьківського облікового запису в інформаційній системі школи:
__ASSOCIATED_STUDENTS__'
WHERE modname='Custom/CreateParents.php';

UPDATE templates
SET template='Шановний(на) __PARENT_NAME__,

Для вас створено батьківський обліковий запис у школі __SCHOOL_ID__, для доступу до інформації про школу й наступних учнів:
__ASSOCIATED_STUDENTS__

Ваші дані для входу:
Псевдонім: __USERNAME__
Пароль: __PASSWORD__

Посилання на сайт інформаційної системи школи та інструкції щодо доступу до неї доступні на сайті школи.'
WHERE modname='Custom/NotifyParents.php';


--
-- Name: students; Type: TABLE; Schema: public; Owner: rosariosis; Tablespace:
--

UPDATE student_field_categories
SET title='General Info|uk_UA.utf8:Загальні відомості'
WHERE id=1;

UPDATE student_field_categories
SET title='Medical|uk_UA.utf8:Медпункт'
WHERE id=2;

UPDATE student_field_categories
SET title='Addresses & Contacts|uk_UA.utf8:Адреси та контакти'
WHERE id=3;

UPDATE student_field_categories
SET title='Comments|uk_UA.utf8:Коментарі'
WHERE id=4;

UPDATE student_field_categories
SET title='Food Service|uk_UA.utf8:Їдальня'
WHERE id=5;


--
-- Data for Name: staff_field_categories; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff_field_categories
SET title='General Info|uk_UA.utf8:Загальна інформація'
WHERE id=1;

UPDATE staff_field_categories
SET title='Schedule|uk_UA.utf8:Розклад'
WHERE id=2;

UPDATE staff_field_categories
SET title='Food Service|uk_UA.utf8:Їдальня'
WHERE id=3;


--
-- Data for Name: custom_fields; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE custom_fields
SET title='Gender|uk_UA.utf8:Стать', select_options='Чоловіча
Жіноча'
WHERE id=200000000;

UPDATE custom_fields
SET title='Ethnicity|uk_UA.utf8:Етнічне походження', select_options='Білошкірий
Темношкірий
Інше'
WHERE id=200000001;

UPDATE custom_fields
SET title='Common Name|uk_UA.utf8:По батькові'
WHERE id=200000002;

UPDATE custom_fields
SET title='Identification Number|uk_UA.utf8:Ідентифікаційний номер'
WHERE id=200000003;

UPDATE custom_fields
SET title='Birthdate|uk_UA.utf8:Дата народження'
WHERE id=200000004;

UPDATE custom_fields
SET title='Language|uk_UA.utf8:Мова', select_options='Українська
Англійська'
WHERE id=200000005;

UPDATE custom_fields
SET title='Physician|uk_UA.utf8:Лікар'
WHERE id=200000006;

UPDATE custom_fields
SET title='Physician Phone|uk_UA.utf8:Номер телефона лікаря'
WHERE id=200000007;

UPDATE custom_fields
SET title='Preferred Hospital|uk_UA.utf8:Переважна лікарня'
WHERE id=200000008;

UPDATE custom_fields
SET title='Comments|uk_UA.utf8:Коментарі'
WHERE id=200000009;

UPDATE custom_fields
SET title='Has Doctor''s Note|uk_UA.utf8:Має довідку від лікаря'
WHERE id=200000010;

UPDATE custom_fields
SET title='Doctor''s Note Comments|uk_UA.utf8:Коментарі до довідки від лікаря'
WHERE id=200000011;


--
-- Data for Name: staff_fields; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff_fields
SET title='Email Address|uk_UA.utf8:Адреса електронної пошти'
WHERE id=200000000;

UPDATE staff_fields
SET title='Phone Number|uk_UA.utf8:Номер телефону'
WHERE id=200000001;


--
-- Data for Name: school_gradelevels; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE school_gradelevels
SET short_name='1а', title='1а'
WHERE id=1;

UPDATE school_gradelevels
SET short_name='2а', title='2а'
WHERE id=2;

UPDATE school_gradelevels
SET short_name='3а', title='3а'
WHERE id=3;

UPDATE school_gradelevels
SET short_name='4а', title='4а'
WHERE id=4;

UPDATE school_gradelevels
SET short_name='5а', title='5а'
WHERE id=5;

UPDATE school_gradelevels
SET short_name='6а', title='6а'
WHERE id=6;

UPDATE school_gradelevels
SET short_name='7а', title='7а'
WHERE id=7;

UPDATE school_gradelevels
SET short_name='8а', title='8а'
WHERE id=8;

UPDATE school_gradelevels
SET short_name='9а', title='9а'
WHERE id=9;


--
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: centrecolrosbog
--

UPDATE students
SET last_name='Іванченко', first_name='Іван', custom_200000000='Чоловіча', custom_200000001='Інше', custom_200000005='Українська'
WHERE student_id=1;


--
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff
SET last_name='Адміністратор'
WHERE staff_id=1;

UPDATE staff
SET last_name='Вчитель'
WHERE staff_id=2;

UPDATE staff
SET last_name='Батько'
WHERE staff_id=3;


--
-- Data for Name: attendance_codes; Type: TABLE DATA; Schema: public; Owner: rosariosis
--


UPDATE attendance_codes
SET title='Відсутній (немає)', short_name='Н'
WHERE id=1;

UPDATE attendance_codes
SET title='Присутній', short_name='П'
WHERE id=2;

UPDATE attendance_codes
SET title='Запізнення', short_name='З'
WHERE id=3;

UPDATE attendance_codes
SET title='Відсутність щодо поваж. причини', short_name='ПП'
WHERE id=4;


--
-- Data for Name: discipline_field_usage; Type: TABLE DATA;
--

UPDATE discipline_field_usage
SET title='Батьки, з якими зв''язався вчитель'
WHERE id=1;

UPDATE discipline_field_usage
SET title='Батьки, з якими зв''язався адміністратор'
WHERE id=2;

UPDATE discipline_field_usage
SET title='Коментарі'
WHERE id=3;

UPDATE discipline_field_usage
SET title='Порушення', select_options='Відсутність на заняттях
Образи, вульгарність, ненормативна лексика
Не слухає (неслухняність, неповажна поведінка)
Знаходження у стані алкогольного чи наркотичного сп''яніння
Розмови без сенсу
Приставання
Бійки
Інше'
WHERE id=4;

UPDATE discipline_field_usage
SET title='Санкція', select_options='10 хвилин
20 хвилин
30 хвилин
Виключення розглянуто'
WHERE id=5;

UPDATE discipline_field_usage
SET title='Виключення (секретаріат)', select_options='Півдня
Затримання у школі
1 день
2 дні
3 дні
5 днів
7 днів
Виключення'
WHERE id=6;


--
-- Data for Name: report_card_comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

UPDATE report_card_comments
SET title='^n не засвоює уроки'
WHERE id=1;

UPDATE report_card_comments
SET title='^n не робить свою домашню роботу'
WHERE id=2;

UPDATE report_card_comments
SET title='^n має позитивний вплив'
WHERE id=3;


--
-- Data for Name: food_service_categories; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_categories
SET title='Їжа'
WHERE category_id=1;


--
-- Data for Name: food_service_items; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_items
SET description='Харчування для учня'
WHERE item_id=1;

UPDATE food_service_items
SET description='Молоко'
WHERE item_id=2;

UPDATE food_service_items
SET description='Бутерброд'
WHERE item_id=3;

UPDATE food_service_items
SET description='Піца'
WHERE item_id=4;


--
-- Data for Name: food_service_menus; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_menus
SET title='Харчування'
WHERE menu_id=1;


--
-- Data for Name: resources; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE resources
SET title='Роздрукувати посібник користувача', link='Help.php'
WHERE id=1;

UPDATE resources
SET title='Короткий посібник з налаштування', link='https://www.rosariosis.org/quick-setup-guide/'
WHERE id=2;

UPDATE resources
SET title='Форум', link='https://www.rosariosis.org/forum/'
WHERE id=3;

UPDATE resources
SET title='Зробити свій внесок', link='https://www.rosariosis.org/contribute/'
WHERE id=4;

UPDATE resources
SET title='Повідомити про помилку'
WHERE id=5;
