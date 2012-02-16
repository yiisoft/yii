Yii erweitern
=============

Eigentlich erweitert man Yii bereits, wenn man eine ganz normale Anwendung damit
entwickelt. Erstellt man z.B. einen neuen Controller, so leitet man diesen von
der [CController]-Klasse ab. Baut man ein neues Widget, erweitert man
[CWidget] bzw. eine andere bestehende Widgetklasse. Von einer richtigen
*Erweiterung* (engl.: extension) sprechen wir aber erst, wenn der neue Code so
strukuriert wurde, dass auch andere ihn wiederverwenden können.

Eine solche Erweiterung erfüllt in der Regel eine bestimmte Aufgabe. Bei Yii
unterscheiden wir folgende Typen:

 * [Anwendungskomponente](/doc/guide/basics.application#application-component)
 * [Behavior](/doc/guide/basics.component#component-behavior)
 * [Widget](/doc/guide/basics.view#widget)
 * [Controller](/doc/guide/basics.controller)
 * [Action](/doc/guide/basics.controller#action)
 * [Filter](/doc/guide/basics.controller#filter)
 * [Konsolenbefehl](/doc/guide/topics.console)
 * Validator: Ein Validator ist eine von [CValidator] abgeleitete Komponente.
 * Helper: Ein Helper (Helfer) ist eine Klasse, die nur statische
Methoden enthält. Sie sind quasi globale Funktionen, die den Klassennamen als
Namespace verwenden.
 * [Modul](/doc/guide/basics.module): Ein Modul ist eine in sich geschlossene
Softwareeinheit, die aus [Models](/doc/guide/basics.model),
[Views](/doc/guide/basics.view), [Controllern](/doc/guide/basics.controller)
und anderen Komponenten besteht. In vielerlei Hinsicht erinnert ein
Modul an eine [Applikation](/doc/guide/basics.application). Der wesentliche
Unterschied besteht darin, dass ein Modul nicht für sich allein betrieben werden 
kann und in eine Applikation eingebettet werden muss. Man könnte zum Beispiel
ein Modul zur Benutzerverwaltung verwenden.

Eine Erweiterung kann auch eine Komponente sein, die in keine dieser
Kategorien fällt. Yii ist nämlich so konzipiert, dass fast alles 
auf individuelle Bedürfnisse hin erweitert oder angepasst werden kann.

<div class="revision">$Id: extension.overview.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>
