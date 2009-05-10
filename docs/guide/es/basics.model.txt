Modelo (Model)
==============

Un modelo es una instancia de [CModel] y de las clases que lo heredan.
Los modelos son utilizados para mantener los datos y sus reglas de negocio
relevantes.

Un modelo representa un solo objeto de datos. El mismo puede ser una fila en una
tabla de base de datos o un formulario de ingresos por usuario. Cada campo del 
objeto de datos esta representado por un atributo en el modelo. El atributo tiene
una etiqueta y esta se puede validar contra un juego de reglas.

Yii implementa dos tipos de modelos: modelo de formulario y active record (registro activo).
Ambos extienden de la misma clase base [CModel].

Un modelo formulario es una instancia de [CFormModel]. El modelo formulario es
utilizado para mantener la colección de datos de las entradas del usuario.
Esos datos coleccionados, utilizados y descartados. Por ejemplo, en una página
de login, nosotros podemos utilizar un modelo de formulario para representar
la información del nombre de usuario y su contraseña que son provistas por 
un usuario final. Para más detalles por favor refierase a 
[Trabajando con formularios](/doc/guide/form.model)

Active Record (AR) es un patron de diseño utilizado para abstraer la base de datos de una 
forma orientada a objetos. Cada objeto AR es una instancia de [CActiveRecord] o una de las
clases que lo heredan, representando una única fila de la tabla de base de datos.
Los campos de la fila son representados por propiedades del objeto AR. Puede encontrar más
información de AR en [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 162 2008-11-05 12:44:08Z sebathi $</div>