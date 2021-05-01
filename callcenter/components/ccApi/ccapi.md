Создаем внутренее HTTP апи. Для интеграции с CRM

1. Инициилзация звонка /api/MakeCall
После Вызова астериск инициализирует звонок на этого оператора и вернет в ответ call_id (id звонка), когда оператор снимает трубку мы делаем звонок клиенту.
Запись разговора мы привязываем к id заказа
для инициализации звонка, с указанием таких параметром:
- sip - sip-номер оператора
- phone - номер клиента
- order_id - номер заказа
- external_key - внешний ключ звонка. (в будущем будет удобно связывать с своей срм звонки) всегда должен быть уникальный.
пример:
http://api.callcenter.mobileconvert.ru/api/makeCall.xml?sip=1000&phone=380639920000&order_id=15&external_key=24
В ответ получите либо call_id, либо ошибку что звонок совершить не возможно.

2. История записей разговоров /api/RecordsList
Для получения истории записей с сортировкой по дате добавления можно использовать метод RecordsList
парамеры:
- page - страница
- per_page - элементов на страницу
примеры:
http://api.callcenter.mobileconvert.ru/api/recordList.json
http://api.callcenter.mobileconvert.ru/api/recordList.json?page=2&per_page=5



3. Записи разговоров по звонку /api/OrderRecords
для получения всех звонков и записей этих звонокв по id заказа, параметры
- order_id - номер заказа
http://api.callcenter.mobileconvert.ru/api/orderRecords.json?order_id=324234



4. История статусов звонка(ов) по заказу /api/OrderHistory
Для получения цепочки статусов по заказу можно использовать метод OrderHistory
Параметры:
- order_id - id заказа
- group_by_call - группировка по звонкам. если 1 то группируем по звонкам, если 0 то в перемешку все звонки по заказам.
Доступные статусы в цепочке статусов внутри заказа:
 CALL - инициилизация звонка
 CLIENT_NOANSWER - клиент не взял трубку
 CLIENT_BUSY - клиент сбросил
 CLIENT_CANCEL - клиент положил трубку до связи с оператором
 CLIENT_PICKUP - клиент снял трубку
 CLIENT_CALLTO - звонок клиенту
 OPERATOR_NOANSWER - оператор не взял трубку
 OPERATOR_BUSY - оператор сбросил звонок
 OPERATOR_PICKUP - оператор взял трубку
 OPERATOR_CALLTO - звонок оператору
 OPERATOR_OFFLINE - оператор на которого пошел дозвон сейчас оффлайн.
 ERROR - ошибка в процессе. Например экстенш в этом направлении отсутствует.
 FINISH - успешное окончание звонка (приходит если клиент был соеденен с оператором, и после этого было уже окончание разговора)
пример:
http://api.callcenter.mobileconvert.ru/api/orderHistory.json?order_id=324234
http://api.callcenter.mobileconvert.ru/api/orderHistory.json?order_id=324234&group_by_call=1
(сквозной список и с группировкой по call)



5. История статусов по звонку/звонкам /api/callHistory
Для выгрузки всех статусов звонка или по конкретному звонку можно использовать метод callHistory. Если не указать параметр call_id получится просто история статусов по звонкам. Удобно для риалтайм парсинга событий.
параметры:
- call_id - id звонка
http://api.callcenter.mobileconvert.ru/api/callHistory.json
http://api.callcenter.mobileconvert.ru/api/callHistory.json?call_id=12
(сквозной список или выборка по конкретному call_id)



6. Список сипов с их статусами /api/sipList (еще не работает)
http://api.callcenter.mobileconvert.ru/api/sipList.json

Оциональные параметры
- per_page - элементов на страницу
- page - страница
- status - фильтр по статусам offline,online,inuse

http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=5
http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=1&status=offline,online,inuse
http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=1&status=offline,online

7. История статусов операторов/оператора /api/OperatorHistory (еще не реализован)
Для того что бы отследить в онлайне оператор,разговаривает или в оффлайне мы ведем логирование действий операторов.
Каждое событие мы записываем в лог. Сортируем от новых статусов к старым
+Доступно фильтрация по оператору