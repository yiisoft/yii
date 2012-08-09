Model
=====

A model is an instance of [CModel] or a class that extends [CModel]. Models are 
used to keep data and their relevant business rules.

A model represents a single data object. It could be a row in a database
table or an html form with user input fields. Each field of the data object is
represented by an attribute of the model. The attribute has a label and can
be validated against a set of rules.

Yii implements two kinds of models:  Form models and active records. They
both extend from the same base class, [CModel].

A form model is an instance of [CFormModel]. Form models are used to store
data collected from user input. Such data is often collected, used and
then discarded. For example, on a login page, we can use a form model to
represent the username and password information that is provided by an end
user. For more details, please refer to [Working with Forms](/doc/guide/form.overview)

Active Record (AR) is a design pattern used to abstract database access in
an object-oriented fashion. Each AR object is an instance of
[CActiveRecord] or of a subclass of that class, representing a single row in a 
database table. The fields in the row are represented as properties of the AR
object. Details about AR can be found in [Active Record](/doc/guide/database.ar).

<div class="revision">$Id$</div>
