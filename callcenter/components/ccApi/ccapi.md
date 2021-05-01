������� ��������� HTTP ���. ��� ���������� � CRM

1. ������������ ������ /api/MakeCall
����� ������ �������� �������������� ������ �� ����� ��������� � ������ � ����� call_id (id ������), ����� �������� ������� ������ �� ������ ������ �������.
������ ��������� �� ����������� � id ������
��� ������������� ������, � ��������� ����� ����������:
- sip - sip-����� ���������
- phone - ����� �������
- order_id - ����� ������
- external_key - ������� ���� ������. (� ������� ����� ������ ��������� � ����� ��� ������) ������ ������ ���� ����������.
������:
http://api.callcenter.mobileconvert.ru/api/makeCall.xml?sip=1000&phone=380639920000&order_id=15&external_key=24
� ����� �������� ���� call_id, ���� ������ ��� ������ ��������� �� ��������.

2. ������� ������� ���������� /api/RecordsList
��� ��������� ������� ������� � ����������� �� ���� ���������� ����� ������������ ����� RecordsList
��������:
- page - ��������
- per_page - ��������� �� ��������
�������:
http://api.callcenter.mobileconvert.ru/api/recordList.json
http://api.callcenter.mobileconvert.ru/api/recordList.json?page=2&per_page=5



3. ������ ���������� �� ������ /api/OrderRecords
��� ��������� ���� ������� � ������� ���� ������� �� id ������, ���������
- order_id - ����� ������
http://api.callcenter.mobileconvert.ru/api/orderRecords.json?order_id=324234



4. ������� �������� ������(��) �� ������ /api/OrderHistory
��� ��������� ������� �������� �� ������ ����� ������������ ����� OrderHistory
���������:
- order_id - id ������
- group_by_call - ����������� �� �������. ���� 1 �� ���������� �� �������, ���� 0 �� � ��������� ��� ������ �� �������.
��������� ������� � ������� �������� ������ ������:
 CALL - ������������� ������
 CLIENT_NOANSWER - ������ �� ���� ������
 CLIENT_BUSY - ������ �������
 CLIENT_CANCEL - ������ ������� ������ �� ����� � ����������
 CLIENT_PICKUP - ������ ���� ������
 CLIENT_CALLTO - ������ �������
 OPERATOR_NOANSWER - �������� �� ���� ������
 OPERATOR_BUSY - �������� ������� ������
 OPERATOR_PICKUP - �������� ���� ������
 OPERATOR_CALLTO - ������ ���������
 OPERATOR_OFFLINE - �������� �� �������� ����� ������ ������ �������.
 ERROR - ������ � ��������. �������� ������� � ���� ����������� �����������.
 FINISH - �������� ��������� ������ (�������� ���� ������ ��� �������� � ����������, � ����� ����� ���� ��� ��������� ���������)
������:
http://api.callcenter.mobileconvert.ru/api/orderHistory.json?order_id=324234
http://api.callcenter.mobileconvert.ru/api/orderHistory.json?order_id=324234&group_by_call=1
(�������� ������ � � ������������ �� call)



5. ������� �������� �� ������/������� /api/callHistory
��� �������� ���� �������� ������ ��� �� ����������� ������ ����� ������������ ����� callHistory. ���� �� ������� �������� call_id ��������� ������ ������� �������� �� �������. ������ ��� �������� �������� �������.
���������:
- call_id - id ������
http://api.callcenter.mobileconvert.ru/api/callHistory.json
http://api.callcenter.mobileconvert.ru/api/callHistory.json?call_id=12
(�������� ������ ��� ������� �� ����������� call_id)



6. ������ ����� � �� ��������� /api/sipList (��� �� ��������)
http://api.callcenter.mobileconvert.ru/api/sipList.json

����������� ���������
- per_page - ��������� �� ��������
- page - ��������
- status - ������ �� �������� offline,online,inuse

http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=5
http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=1&status=offline,online,inuse
http://api.callcenter.mobileconvert.ru/api/sipList.xml?per_page=100&page=1&status=offline,online

7. ������� �������� ����������/��������� /api/OperatorHistory (��� �� ����������)
��� ���� ��� �� ��������� � ������� ��������,������������� ��� � �������� �� ����� ����������� �������� ����������.
������ ������� �� ���������� � ���. ��������� �� ����� �������� � ������
+�������� ���������� �� ���������