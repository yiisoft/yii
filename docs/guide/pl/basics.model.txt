Model
=====

Model jest instancją klasy [CModel] lub klasą pochodną [CModel]. Modeli używamy do 
przechowywania danych oraz odpowiadających im reguł biznesowych.

Model reprezentuje pojedynczy obiekt danych. Może to być wiersz z tabeli bazy 
danych lub formularz html zawierający pola wejściowe. Każde pole obiektu danych 
jest reprezentowane przez atrybut modelu. Atrybut posiada etykietę oraz może zostać 
sprawdzony, czy spełnia zestaw reguł. 

Yii implementuje dwa rodzaje modeli: model formularza oraz model rekordu aktywnego.
Oba dziedziczą z tej samej klasy bazowej [CModel].

Model formularza jest instancją [CFormModel]. Takie modele używane są do przechowywania 
danych zbieranych z danych wejściowych użytkownika. Dane te, często są zbierane, 
używane a następnie porzucane. Na przykład, na stronie logowania, używamy modelu 
formularza do reprezentacji informacji o nazwie użytkownika oraz haśle, które są 
dostarczone przez użytkownika końcowego. Aby uzyskać więcej szczegółów, zobacz 
sekcję [Praca z formularzami](/doc/guide/form.overview).

Rekord aktywny (AR) jest wzorcem projektowym używanym do uzyskania dostępu do abstrakcji 
bazy danych w bardziej obiektowo zorientowany sposób. Każdy obiekt AR jest instancją 
klasy [CActiveRecord] lub jej klas pochodnych, reprezentujących pojedynczy wiersz 
w bazie danych. Pola w wierszu są reprezentowane przez właściwości obiektu AR.
Szczegóły dotyczące AR można znaleźć w sekcji [Rekord aktywny](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>