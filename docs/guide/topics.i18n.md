Internationalization
====================

Internationalization (I18N) refers to the process of designing a software
application so that it can be adapted to various languages and regions
without engineering changes. For Web applications, this is of particular
importance because the potential users may be from worldwide.

Yii provides support for I18N in several aspects.

   - It provides the locale data for each possible language and variant.
   - It provides message and file translation service.
   - It provides locale-dependent date and time formatting.
   - It provides locale-dependent number formatting.

In the following subsections, we will elaborate each of the above aspects.

Locale and Language
-------------------

Locale is a set of parameters that defines the user's language, country
and any special variant preferences that the user wants to see in their
user interface. It is usually identified by an ID consisting of a language
ID and a region ID. For example, the ID `en_US` stands for the locale of
English and United States. For consistency, all locale IDs in Yii are
canonicalized to the format of `LanguageID` or `LanguageID_RegionID`
in lower case (e.g. `en`, `en_us`).

Locale data is represented as a [CLocale] instance. It provides
locale-dependent information, including currency symbols, number symbols,
currency formats, number formats, date and time formats, and date-related
names. Since the language information is already implied in the locale ID,
it is not provided by [CLocale]. For the same reason, we often
interchangeably using the term locale and language.

Given a locale ID, one can get the corresponding [CLocale] instance by
`CLocale::getInstance($localeID)` or `CApplication::getLocale($localeID)`.

> Info: Yii comes with locale data for nearly every language and region.
The data is obtained from [Common Locale Data Repository](http://unicode.org/cldr/) (CLDR). For each locale, only a
subset of the CLDR data is provided as the original data contains a lot of
rarely used information. Users can also supply
their own customized locale data. To do so, configure the [CApplication::localeDataPath]
property with the directory that contains the customized locale data.
Please refer to the locale data files under `framework/i18n/data` in order
to create customized locale data files.

For a Yii application, we differentiate its [target
language|CApplication::language] from [source
language|CApplication::sourceLanguage]. The target language is the language
(locale) of the users that the application is targeted at, while the source
language refers to the language (locale) that the application source files
are written in. Internationalization occurs only when the two languages are
different.

One can configure [target language|CApplication::language] in the
[application configuration](/doc/guide/basics.application#application-configuration), or
change it dynamically before any internationalization occurs.

> Tip: Sometimes, we may want to set the target language as the language
preferred by a user (specified in user's browser preference). To do so, we
can retrieve the user preferred language ID using
[CHttpRequest::preferredLanguage].

Translation
-----------

The most needed I18N feature is perhaps translation, including message
translation and view translation. The former translates a text message to
the desired language, while the latter translates a whole file to the
desired language.

A translation request consists of the object to be translated, the source
language that the object is in, and the target language that the object
needs to be translated to. In Yii, the source language is default to the
[application source language|CApplication::sourceLanguage] while the target
language is default to the [application language|CApplication::language].
If the source and target languages are the same, translation will not
occur.

### Message Translation

Message translation is done by calling [Yii::t()|YiiBase::t]. The method
translates the given message from [source
language|CApplication::sourceLanguage] to [target
language|CApplication::language].

When translating a message, its category has to be specified since a
message may be translated differently under different categories
(contexts). The category `yii` is reserved for messages used by the Yii
framework core code.

Messages can contain parameter placeholders which will be replaced with
the actual parameter values when calling [Yii::t()|YiiBase::t]. For
example, the following message translation request would replace the
`{alias}` placeholder in the original message with the actual alias value.

~~~
[php]
Yii::t('app', 'Path alias "{alias}" is redefined.',
	array('{alias}'=>$alias))
~~~

> Note: Messages to be translated must be constant strings. They should
not contain variables that would change message content (e.g. `"Invalid
{$message} content."`). Use parameter placeholders if a message needs to
vary according to some parameters.

Translated messages are stored in a repository called *message
source*. A message source is represented as an instance of
[CMessageSource] or its child class. When [Yii::t()|YiiBase::t] is invoked,
it will look for the message in the message source and return its
translated version if it is found.

Yii comes with the following types of message sources. You may also extend
[CMessageSource] to create your own message source type.

   - [CPhpMessageSource]: the message translations are stored as key-value
pairs in a PHP array. The original message is the key and the translated
message is the value. Each array represents the translations for a
particular category of messages and is stored in a separate PHP script file
whose name is the category name. The PHP translation files for the same
language are stored under the same directory named as the locale ID. And
all these directories are located under the directory specified by
[basePath|CPhpMessageSource::basePath].

   - [CGettextMessageSource]: the message translations are stored as [GNU
Gettext](http://www.gnu.org/software/gettext/) files.

   - [CDbMessageSource]: the message translations are stored in database
tables. For more details, see the API documentation for [CDbMessageSource].

A message source is loaded as an [application
component](/doc/guide/basics.application#application-component). Yii pre-declares an
application component named [messages|CApplication::messages] to store
messages that are used in user application. By default, the type of this
message source is [CPhpMessageSource] and the base path for storing the PHP
translation files is `protected/messages`.

In summary, in order to use message translation, the following steps are
needed:

   1. Call [Yii::t()|YiiBase::t] at appropriate places;

   2. Create PHP translation files as
`protected/messages/LocaleID/CategoryName.php`. Each file simply returns an
array of message translations. Note, this assumes you are using the default
[CPhpMessageSource] to store the translated messages.

   3. Configure [CApplication::sourceLanguage] and [CApplication::language].

> Tip: The `yiic` tool in Yii can be used to manage message translations
when [CPhpMessageSource] is used as the message source. Its `message` command
can automatically extract messages to be translated from selected source files
and merge them with existing translations if necessary. For more details of using
the `message` command, please run `yiic help message`.

When using [CPhpMessageSource] to manage message source,
messages for an extension class (e.g. a widget, a module) can be specially managed and used. In particular, if a message
belongs to an extension whose class name is `Xyz`, then the message category can be specified
in the format of `Xyz.categoryName`. The corresponding message file will be assumed to be
`BasePath/messages/LanguageID/categoryName.php`, where `BasePath` refers to
the directory that contains the extension class file. And when using `Yii::t()` to
translate an extension message, the following format should be used, instead:

~~~
[php]
Yii::t('Xyz.categoryName', 'message to be translated')
~~~

Yii supports [choice format|CChoiceFormat], which is also known as plural forms. Choice format
refers to choosing a translated according to a given number value. For example,
in English the word 'book' may either take a singular form or a plural form
depending on the number of books, while in other languages, the word may not have
different form (such as Chinese) or may have more complex 	plural form rules
(such as Russian). Choice format solves this problem in a simple yet effective way.

To use choice format, a translated message must consist of a sequence of
expression-message pairs separated by `|`, as shown below:

~~~
[php]
'expr1#message1|expr2#message2|expr3#message3'
~~~

where `exprN` refers to a valid PHP expression which evaluates to a boolean value
indicating whether the corresponding message should be returned. Only the message
corresponding to the first expression that evaluates to true will be returned.
An expression can contain a special variable named `n` (note, it is not `$n`)
which will take the number value passed as the first message parameter. For example,
assuming a translated message is:

~~~
[php]
'n==1#one book|n>1#many books'
~~~

and we are passing a number value 2 in the message parameter array when
calling [Yii::t()|YiiBase::t], we would obtain `many books` as the final
translated message:

~~~
[php]
Yii::t('app', 'n==1#one book|n>1#many books', array(1)));
//or since 1.1.6
Yii::t('app', 'n==1#one book|n>1#many books', 1));
~~~

As a shortcut notation, if an expression is a number, it will be treated as
`n==Number`. Therefore, the above translated message can be also be written as:

~~~
[php]
'1#one book|n>1#many books'
~~~

### Plural forms format

Since version 1.1.6 CLDR-based plural choice format can be used with a simpler
syntax that. It is handy for languages with complex plural form rules.



The rule for English plural forms above can be written in the following way:

~~~
[php]
Yii::t('test', 'cucumber|cucumbers', 1);
Yii::t('test', 'cucumber|cucumbers', 2);
Yii::t('test', 'cucumber|cucumbers', 0);
~~~

The code above will give you:

~~~
cucumber
cucumbers
cucumbers
~~~

If you want to include number you can use the following code.

~~~
[php]
echo Yii::t('test', '{n} cucumber|{n} cucumbers', 1);
~~~

Here `{n}` is a special placeholder holding number passed. It will print `1 cucumber`.

You can pass additional parameters:

~~~
[php]
Yii::t('test', '{username} has a cucumber|{username} has {n} cucumbers',
array(5, '{username}' => 'samdark'));
~~~

and even replace number parameter with something else:

~~~
[php]
function convertNumber($number)
{
	// convert number to word
	return $number;
}

Yii::t('test', '{n} cucumber|{n} cucumbers',
array(5, '{n}' => convertNumber(5)));
~~~

For Russian it will be:
~~~
[php]
Yii::t('app', '{n} cucumber|{n} cucumbers', 62);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1.5);
Yii::t('app', '{n} cucumber|{n} cucumbers', 1);
Yii::t('app', '{n} cucumber|{n} cucumbers', 7);
~~~

with translated message

~~~
[php]
'{n} cucumber|{n} cucumbers' => '{n} огурец|{n} огурца|{n} огурцов|{n} огурца',
~~~

and will give you

~~~
62 огурца
1.5 огурца
1 огурец
7 огурцов
~~~


> Info: to learn about how many values you should supply and in which
 order they should be, please refer to CLDR
 [Language Plural Rules page](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

### File Translation

File translation is accomplished by calling
[CApplication::findLocalizedFile()]. Given the path of a file to be
translated, the method will look for a file with the same name under the
`LocaleID` subdirectory. If found, the file path will be returned;
otherwise, the original file path will be returned.

File translation is mainly used when rendering a view. When calling one of
the render methods in a controller or widget, the view files will be
translated automatically. For example, if the [target
language|CApplication::language] is `zh_cn` while the [source
language|CApplication::sourceLanguage] is `en_us`, rendering a view named
`edit` would resulting in searching for the view file
`protected/views/ControllerID/zh_cn/edit.php`. If the file is found, this
translated version will be used for rendering; otherwise, the file
`protected/views/ControllerID/edit.php` will be rendered instead.

File translation may also be used for other purposes, for example,
displaying a translated image or loading a locale-dependent data file.

Date and Time Formatting
------------------------

Date and time are often in different formats in different countries or
regions. The task of date and time formatting is thus to generate a date or
time string that fits for the specified locale. Yii provides
[CDateFormatter] for this purpose.

Each [CDateFormatter] instance is associated with a target locale. To get
the formatter associated with the target locale of the whole application,
we can simply access the [dateFormatter|CApplication::dateFormatter]
property of the application.

The [CDateFormatter] class mainly provides two methods to format a UNIX
timestamp.

   - [format|CDateFormatter::format]: this method formats the given UNIX
timestamp into a string according to a customized pattern (e.g.
`$dateFormatter->format('yyyy-MM-dd',$timestamp)`).

   - [formatDateTime|CDateFormatter::formatDateTime]: this method formats
the given UNIX timestamp into a string according to a pattern predefined in
the target locale data (e.g. `short` format of date, `long` format of
time).

Number Formatting
-----------------

Like data and time, numbers may also be formatted differently in different
countries or regions. Number formatting includes decimal formatting,
currency formatting and percentage formatting. Yii provides
[CNumberFormatter] for these tasks.

To get the number formatter associated with the target locale of the whole
application, we can access the
[numberFormatter|CApplication::numberFormatter] property of the
application.

The following methods are provided by [CNumberFormatter] to format an
integer or double value.

   - [format|CNumberFormatter::format]: this method formats the given
number into a string according to a customized pattern (e.g.
`$numberFormatter->format('#,##0.00',$number)`).

   - [formatDecimal|CNumberFormatter::formatDecimal]: this method formats
the given number using the decimal pattern predefined in the target locale
data.

   - [formatCurrency|CNumberFormatter::formatCurrency]: this method
formats the given number and currency code using the currency pattern
predefined in the target locale data.

   - [formatPercentage|CNumberFormatter::formatPercentage]: this method
formats the given number using the percentage pattern predefined in the
target locale data.

<div class="revision">$Id$</div>
