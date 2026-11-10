--
-- PostgreSQL & MySQL data update
--
-- Translates database fields to Russian
--
-- Note: Uncheck "Paginate results" when importing with phpPgAdmin
--

--
-- Data for Name: schools; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE schools
SET title='Школа (RU)', address='Улица Ивана Иванова, д. 1', city='Иваново', state=NULL, zipcode='100000', principal='Иванов И. И.', www_address='www.rosariosis.org', reporting_gp_scale=10
WHERE id=1;


--
-- Data for Name: attendance_calendars; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE attendance_calendars
SET title='Основной'
WHERE calendar_id=1;


--
-- Data for Name: config; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE config
SET config_value='Rosario Student Information System|ru_RU.utf8:Учебная информационная система Rosario'
WHERE title='TITLE';

UPDATE config
SET config_value='₽'
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
SET title='Выпуск', short_name='ВЫП'
WHERE id=1;

UPDATE student_enrollment_codes
SET title='Исключение', short_name='ИСКЛ'
WHERE id=2;

UPDATE student_enrollment_codes
SET title='Начало года', short_name='НАЧГОДА'
WHERE id=3;

UPDATE student_enrollment_codes
SET title='Другой район', short_name='ДРРАЙОН'
WHERE id=4;

UPDATE student_enrollment_codes
SET title='Перенос', short_name='TRAN'
WHERE id=5;

UPDATE student_enrollment_codes
SET title='Перенос', short_name='MAN'
WHERE id=6;


--
-- Data for Name: report_card_grade_scales; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE report_card_grade_scales
SET title='Главное', gp_scale=10, gp_passing_value=5
WHERE id=1;


--
-- Data for Name: report_card_grades; Type: TABLE DATA; Schema: public; Owner: postgres
--


UPDATE report_card_grades
SET title='10.0', gpa_value=10.0, break_off=97.5, comment='Отлично'
WHERE id=1;

UPDATE report_card_grades
SET title='9.5', gpa_value=9.5, break_off=92.5, comment='Отлично'
WHERE id=2;

UPDATE report_card_grades
SET title='9.0', gpa_value=9.0, break_off=87.5, comment='Отлично'
WHERE id=3;

UPDATE report_card_grades
SET title='8.5', gpa_value=8.5, break_off=82.5, comment='Отлично'
WHERE id=4;

UPDATE report_card_grades
SET title='8.0', gpa_value=8.0, break_off=77.5, comment='Хорошо'
WHERE id=5;

UPDATE report_card_grades
SET title='7.5', gpa_value=7.5, break_off=72.5, comment='Хорошо'
WHERE id=6;

UPDATE report_card_grades
SET title='7.0', gpa_value=7.0, break_off=67.5, comment='Хорошо'
WHERE id=7;

UPDATE report_card_grades
SET title='6.5', gpa_value=6.5, break_off=62.5, comment='Удовлетворительно'
WHERE id=8;

UPDATE report_card_grades
SET title='6.0', gpa_value=6.0, break_off=57.5, comment='Удовлетворительно'
WHERE id=9;

UPDATE report_card_grades
SET title='5.5', gpa_value=5.5, break_off=52.5, comment='Неудовлетворительно'
WHERE id=10;

UPDATE report_card_grades
SET title='5.0', gpa_value=5.0, break_off=47.5, comment='Неудовлетворительно'
WHERE id=11;

UPDATE report_card_grades
SET title='4.5', gpa_value=4.5, break_off=42.5, comment='Слабо'
WHERE id=12;

UPDATE report_card_grades
SET title='4.0', gpa_value=4.0, break_off=37.5, comment='Слабо'
WHERE id=13;

UPDATE report_card_grades
SET title='3.5', gpa_value=3.5, break_off=32.5, comment='Слабо'
WHERE id=14;

UPDATE report_card_grades
SET title='3.0', gpa_value=3.0, break_off=27.5, comment='Слабо'
WHERE id=15;

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '2.5', 16, 2.5, 22.5, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '2.0', 17, 2.0, 17.5, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '1.5', 18, 1.5, 12.5, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '1.0', 19, 1.0, 7.5, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '0.5', 20, 0.5, 2.5, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, '0.0', 21, 0.0, 0, 'Плохо', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, 'I', 22, 0.0, 0, 'Не сдано', 1, NULL);

INSERT INTO report_card_grades (syear, school_id, title, sort_order, gpa_value, break_off, comment, grade_scale_id, unweighted_gp)
VALUES ((SELECT syear FROM schools WHERE id=1 LIMIT 1), 1, 'Н/Д', 23, NULL, NULL, NULL, 1, NULL);


--
-- Data for Name: school_marking_periods; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE school_marking_periods
SET title='Весь год', short_name='Год'
WHERE marking_period_id=1;

UPDATE school_marking_periods
SET title='1 семестр', short_name='1С'
WHERE marking_period_id=2;

UPDATE school_marking_periods
SET title='2 семестр', short_name='2С'
WHERE marking_period_id=3;

UPDATE school_marking_periods
SET title='1 четверть', short_name='1Ч'
WHERE marking_period_id=4;

UPDATE school_marking_periods
SET title='2 четверть', short_name='2Ч'
WHERE marking_period_id=5;

UPDATE school_marking_periods
SET title='3 четверть', short_name='3Ч'
WHERE marking_period_id=6;

UPDATE school_marking_periods
SET title='4 четверть', short_name='4Ч'
WHERE marking_period_id=7;


--
-- Data for Name: school_periods; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE school_periods
SET title='Весь день', short_name='ВЕСЬДЕНЬ'
WHERE period_id=1;

UPDATE school_periods
SET title='Утро', short_name='УТРО'
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
<div style="text-align: center;"><span style="font-size: xx-large;"><strong>__SCHOOL_ID__</strong><br /></span><br /><span style="font-size: xx-large;">Награждается<br /><br /></span></div>
<div style="text-align: center;"><span style="font-size: xx-large;"><strong>__FIRST_NAME__ __LAST_NAME__</strong><br /><br /></span></div>
<div style="text-align: center;"><span style="font-size: xx-large;">за высокую<br />успеваемость</span></div>'
WHERE modname='Grades/HonorRoll.php';

UPDATE templates
SET template='<div style="text-align: center;">__CLIPART__<br /><br /><strong><span style="font-size: xx-large;">__SCHOOL_ID__<br /></span></strong><br /><span style="font-size: xx-large;">Награждается<br /><br /></span></div>
<div style="text-align: center;"><strong><span style="font-size: xx-large;">__FIRST_NAME__ __LAST_NAME__<br /><br /></span></strong></div>
<div style="text-align: center;"><span style="font-size: xx-large;">за отличную учёбу по предмету<br />__SUBJECT__</span></div>'
WHERE modname='Grades/HonorRollSubject.php';

UPDATE templates
SET template='<h2 style="text-align: center;">Сертификат об обучении</h2>
<p>Согласно информации от директора и секретариата:</p>
<p>Что __FIRST_NAME__ __LAST_NAME__ с идентификатором __SSECURITY__ проходит обучение в этом учреждении, в классе __GRADE_ID__ в учебном году __YEAR__ и получил(а) оценки.</p>
<p>Учащийся(аяся) переходит в класс __NEXT_GRADE_ID__.</p>
<p>__BLOCK2__</p>
<p>&nbsp;</p>
<table style="border-collapse: collapse; width: 100%;" border="0" cellpadding="10"><tbody><tr>
<td style="width: 50%; text-align: center;"><hr />
<p>Подпись</p>
<p>&nbsp;</p><hr />
<p>Имя</p></td>
<td style="width: 50%; text-align: center;"><hr />
<p>Подпись</p>
<p>&nbsp;</p><hr />
<p>Имя</p></td></tr></tbody></table>'
WHERE modname='Grades/Transcripts.php';

UPDATE templates
SET template='Уважаемый(ая) __PARENT_NAME__,

Для вас создана родительская учётная запись в школе __SCHOOL_ID__ для доступа к информации об этой школе и учащихся:
__ASSOCIATED_STUDENTS__

Ваши данные для входа:
Псевдоним: __USERNAME__
Пароль: __PASSWORD__

Ссылка на сайт информационной системы школы и инструкции по доступу к ней доступны на сайте школы.__BLOCK2__Уважаемый(ая) __PARENT_NAME__,

Следующие учащиеся были привязаны к вашей родительской учётной записи в информационной системе школы:
__ASSOCIATED_STUDENTS__'
WHERE modname='Custom/CreateParents.php';

UPDATE templates
SET template='Уважаемый(ая) __PARENT_NAME__,

Для вас создана родительская учётная запись в школе __SCHOOL_ID__, для доступа к информации о школе и следующих учащихся:
__ASSOCIATED_STUDENTS__

Ваши данные для входа:
Псевдоним: __USERNAME__
Пароль: __PASSWORD__

Ссылка на сайт информационной системы школы и инструкции по доступу к ней доступны на сайте школы.'
WHERE modname='Custom/NotifyParents.php';


--
-- Name: students; Type: TABLE; Schema: public; Owner: rosariosis; Tablespace:
--

UPDATE student_field_categories
SET title='General Info|ru_RU.utf8:Общие сведения'
WHERE id=1;

UPDATE student_field_categories
SET title='Medical|ru_RU.utf8:Медпункт'
WHERE id=2;

UPDATE student_field_categories
SET title='Addresses & Contacts|ru_RU.utf8:Адреса и контакты'
WHERE id=3;

UPDATE student_field_categories
SET title='Comments|ru_RU.utf8:Комментарии'
WHERE id=4;

UPDATE student_field_categories
SET title='Food Service|ru_RU.utf8:Столовая'
WHERE id=5;


--
-- Data for Name: staff_field_categories; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff_field_categories
SET title='General Info|ru_RU.utf8:Общая информация'
WHERE id=1;

UPDATE staff_field_categories
SET title='Schedule|ru_RU.utf8:Расписание'
WHERE id=2;

UPDATE staff_field_categories
SET title='Food Service|ru_RU.utf8:Столовая'
WHERE id=3;


--
-- Data for Name: custom_fields; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE custom_fields
SET title='Gender|ru_RU.utf8:Пол', select_options='Мужской
Женский'
WHERE id=200000000;

UPDATE custom_fields
SET title='Ethnicity|ru_RU.utf8:Этническое происхождение', select_options='Белокожий
Темнокожий
Другое'
WHERE id=200000001;

UPDATE custom_fields
SET title='Common Name|ru_RU.utf8:Отчество'
WHERE id=200000002;

UPDATE custom_fields
SET title='Identification Number|ru_RU.utf8:Идентификационный номер'
WHERE id=200000003;

UPDATE custom_fields
SET title='Birthdate|ru_RU.utf8:Дата рождения'
WHERE id=200000004;

UPDATE custom_fields
SET title='Language|ru_RU.utf8:Язык', select_options='Русский
Английский'
WHERE id=200000005;

UPDATE custom_fields
SET title='Physician|ru_RU.utf8:Врач'
WHERE id=200000006;

UPDATE custom_fields
SET title='Physician Phone|ru_RU.utf8:Номер телефона врача'
WHERE id=200000007;

UPDATE custom_fields
SET title='Preferred Hospital|ru_RU.utf8:Предпочитаемая больница'
WHERE id=200000008;

UPDATE custom_fields
SET title='Comments|ru_RU.utf8:Комментарии'
WHERE id=200000009;

UPDATE custom_fields
SET title='Has Doctor''s Note|ru_RU.utf8:Имеет справку от врача'
WHERE id=200000010;

UPDATE custom_fields
SET title='Doctor''s Note Comments|ru_RU.utf8:Комментарии к справке врача'
WHERE id=200000011;


--
-- Data for Name: staff_fields; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff_fields
SET title='Email Address|ru_RU.utf8:Адрес электронной почты'
WHERE id=200000000;

UPDATE staff_fields
SET title='Phone Number|ru_RU.utf8:Номер телефона'
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
SET last_name='Иванов', first_name='Иван', custom_200000000='Мужской', custom_200000001='Другое', custom_200000005='Русский'
WHERE student_id=1;


--
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE staff
SET last_name='Администратор'
WHERE staff_id=1;

UPDATE staff
SET last_name='Учитель'
WHERE staff_id=2;

UPDATE staff
SET last_name='Родитель'
WHERE staff_id=3;


--
-- Data for Name: attendance_codes; Type: TABLE DATA; Schema: public; Owner: rosariosis
--


UPDATE attendance_codes
SET title='Отсутствует (нет)', short_name='Н'
WHERE id=1;

UPDATE attendance_codes
SET title='Присутствует', short_name='П'
WHERE id=2;

UPDATE attendance_codes
SET title='Опоздание', short_name='О'
WHERE id=3;

UPDATE attendance_codes
SET title='Отсутствие по уваж. причине', short_name='УП'
WHERE id=4;


--
-- Data for Name: discipline_field_usage; Type: TABLE DATA;
--

UPDATE discipline_field_usage
SET title='Родители, с которыми связался учитель'
WHERE id=1;

UPDATE discipline_field_usage
SET title='Родители, с которыми связался администратор'
WHERE id=2;

UPDATE discipline_field_usage
SET title='Комментарии'
WHERE id=3;

UPDATE discipline_field_usage
SET title='Нарушение', select_options='Отсутствует на занятиях
Оскорбления, вульгарность, ненормативная лексика
Не слушает (неповиновение, неуважительное поведение)
Нахождение в состоянии алкогольного или наркотического опьянения
Разговоры без смысла
Приставания
Драки
Другое'
WHERE id=4;

UPDATE discipline_field_usage
SET title='Санкция', select_options='10 минут
20 минут
30 минут
Исключение рассмотрено'
WHERE id=5;

UPDATE discipline_field_usage
SET title='Исключения (секретариат)', select_options='Полдня
Задержание в школе
1 день
2 дня
3 дня
5 дней
7 дней
Исключение'
WHERE id=6;


--
-- Data for Name: report_card_comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

UPDATE report_card_comments
SET title='^n не усваивает уроки'
WHERE id=1;

UPDATE report_card_comments
SET title='^n не делает свою домашнюю работу'
WHERE id=2;

UPDATE report_card_comments
SET title='^n имеет положительное влияние'
WHERE id=3;


--
-- Data for Name: food_service_categories; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_categories
SET title='Еда'
WHERE category_id=1;


--
-- Data for Name: food_service_items; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_items
SET description='Питание для учащегося'
WHERE item_id=1;

UPDATE food_service_items
SET description='Молоко'
WHERE item_id=2;

UPDATE food_service_items
SET description='Бутерброд'
WHERE item_id=3;

UPDATE food_service_items
SET description='Пицца'
WHERE item_id=4;


--
-- Data for Name: food_service_menus; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE food_service_menus
SET title='Питание'
WHERE menu_id=1;


--
-- Data for Name: resources; Type: TABLE DATA; Schema: public; Owner: rosariosis
--

UPDATE resources
SET title='Распечатать руководство пользователя', link='Help.php'
WHERE id=1;

UPDATE resources
SET title='Краткое руководство по настройке', link='https://www.rosariosis.org/quick-setup-guide/'
WHERE id=2;

UPDATE resources
SET title='Форум', link='https://www.rosariosis.org/forum/'
WHERE id=3;

UPDATE resources
SET title='Внести свой вклад', link='https://www.rosariosis.org/contribute/'
WHERE id=4;

UPDATE resources
SET title='Сообщить об ошибке'
WHERE id=5;
