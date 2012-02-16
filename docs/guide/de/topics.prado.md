Alternative Templatesyntax
==========================

Yii erlaubt es dem Entwickler, seine eigene bevorzugte Templatesyntax 
(z.B. von Prado oder Smarty) für Controller- und Widgetviews zu verwenden.
Dazu  muss eine [viewRenderer|CWebApplication::viewRenderer]-Komponente
erstellt bzw. konfiguriert werden. Diese Komponente fängt die Aufrufe von
[CBaseController::renderFile] ab, kompiliert die Viewdatei in der 
entsprechenden Syntax und rendert das Ergebnis.

> Info: Es wird empfohlen, die alternative Syntax nur bei Views einzusetzen,
die kaum wiederverwendet werden. Andernfalls müssten alle, die den View
wiederverwenden möchten, die selbe alternative Syntax in ihrer Anwendung
einsetzen.

Wir zeigen im Folgenden, wie man den [CPradoViewRenderer] verwendet, um mit
einer ähnlichen Templatesyntax wie in [Prado](http://www.pradosoft.com/) zu
arbeiten. Möchte man seinen eigenen Viewrenderer verwirklichen, ist der 
[CPradoViewRenderer] ein guter Ausgangspunkt dafür.

Einsatz des `CPradoViewRenderer`
--------------------------------

Um den [CPradoViewRenderer] zu verwenden, muss folgende Anwendungskomponente
konfiguriert werden:

~~~
[php]
return array(
	'components'=>array(
		......,
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',
		),
	),
);
~~~

Standardmäßig kompiliert der [CPradoViewRenderer] die Viewdateien und
speichert die resultierenden PHP-Dateien im
[Runtime](/doc/guide/basics.convention#directory)-Verzeichnis der Anwendung.
Diese PHP-Dateien werden nur dann neu generiert, wenn die Quelldatei sich
geändert hat. Auf diese Weise verringert der Einsatz von [CPradoViewRenderer] die
Performance nur unwesentlich.

> Tip|Tipp: Der [CPradoViewRenderer] führt hauptsächlich vereinfachende 
Templatetags für Views ein. Daneben kann man aber auch immer noch normalen
PHP-Code in Views verwenden.

Folgende Templatetags werden vom [CPradoViewRenderer] unterstützt:

### Kurze PHP-Tags

Kurze PHP-Tags sind Kurzschreibweisen für PHP-Ausdrücke und
-Anweisungen in einem View. Der Tag `<%= Ausdruck %>` wird zu `<?php
echo Ausdruck ?>` übersetzt, während `<% Anweisung %>` zu
`<?php Anweisung ?>` übersetzt wird. So wird

~~~
[php]
<%= CHtml::textField($name,'value'); %>
<% foreach($models as $model): %>
~~~

übersetzt zu

~~~
[php]
<?php echo CHtml::textField($name,'value'); ?>
<?php foreach($models as $model): ?>
~~~

### Komponententags

Komponententags dienen zum Einfügen eines
[Widgets](/doc/guide/basics.view#widget) in einem View. Sie verwenden diese
Syntax: 

~~~
[php]
<com:WidgetKlasse eigenschaft1=wert1 eigenschaft2=wert2...>
	// Eingebetteter Inhalt des Widgets
</com:WidgetKlasse>

// Ein Widget ohne eingebetteten Inhalt
<com:WidgetKlasse eigenschaft1=wert1 eigenschaft2=wert2.../>
~~~

wobei `WidgetKlasse` den Klassennamen oder
[Pfadalias](/doc/guide/basics.namespace) des Widgets definiert und die
Startwerte für Eigenschaften entweder in doppelten Anführungszeichen oder als
PHP-Ausdruck in geschweiften Klammern angegeben werden können.

~~~
[php]
<com:CCaptcha captchaAction="captcha" showRefreshButton={false} />
~~~

würde damit übersetzt zu

~~~
[php]
<?php $this->widget('CCaptcha', array(
	'captchaAction'=>'captcha',
	'showRefreshButton'=>false)); ?>
~~~

> Note|Hinweis: Der Wert für `showRefreshButton` wird als `{false}` statt
`"false"` angegeben, da letzteres einen String statt eines boole'schen Wertes
darstellen würde.

### Cachetags

Cachetags sind Abkürzungen zum [Cachen von
Fragmenten](/doc/guide/caching.fragment). Die Syntax lautet

~~~
[php]
<cache:abschnittID eigenschaft1=wert1 eigenschaft2=wert2...>
	// Zu cachender Inhalt
</cache:abschnittID>
~~~

wobei `abschnittID` ein eindeutiger Bezeichner für den zu cachenden Inhalt
sein sollte und die Eigenschafts-Werte-Paare zum konfigurieren des Caches
dienen. Zum Beispiel würde

~~~
[php]
<cache:profil duration={3600}>
	// Informationen zum Benutzerprofil
</cache:profil >
~~~

übersetzt werden in

~~~
[php]
<?php if($this->beginCache('profile', array('duration'=>3600))): ?>
	// Informationen zum Benutzerprofil
<?php $this->endCache(); endif; ?>
~~~

### Cliptags

Wie Cachetags sind auch Cliptags Abkürzungen um [CBaseController::beginClip]
und [CBaseController::endClip] in einem View aufzurufen. Die Syntax lautet

~~~
[php]
<clip:clipID>
	// Inhalt des Clips
</clip:clipID >
~~~

wobei `clipID` ein eindeutiger Bezeichner für den Clipinhalt ist.
Cliptags werden übersetzt zu

~~~
[php]
<?php $this->beginClip('clipID'); ?>
	// Inhalt des Clips
<?php $this->endClip(); ?>
~~~

### Kommentartags

Kommentartags sind für Viewkommentare gedacht und nur für Entwickler
sichtbar. Kommentartags werden vor der Anzeige aus dem View entfernt. Die
Syntax lautet hier

~~~
[php]
<!---
View-Kommentare, der später entfernt wird
--->
~~~

Mischen von Templateformaten
----------------------------

Seit Version 1.1.2 kann die alternative Templatesyntax auch gemeinsam mit der
normalen PHP-Syntax von Viewdateien eingesetzt werden. Dazu muss
[CViewRenderer::fileExtension] des Viewrenderers auf einen anderen Wert als
`.php` gesetzt werden. Konfiguriert man diese Eigenschaft z.B. auf `.tpl`,
werden alle Dateien mit der Endung `.tpl` mit dem angegebenen Viewrenderer
dargestellt. Alle anderen Dateien werden als normale PHP-Viewdateien
behandelt.

<div class="revision">$Id: topics.prado.txt 3226 2011-05-18 10:37:47Z mdomba $</div>
