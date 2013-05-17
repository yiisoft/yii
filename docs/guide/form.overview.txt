Working with Form
=================

Collecting user data via HTML forms is one of the major tasks in Web
application development. Besides designing forms, developers need to
populate the form with existing data or default values, validate user
input, display appropriate error messages for invalid input, and save the
input to persistent storage. Yii greatly simplifies this workflow with its
MVC architecture.

The following steps are typically needed when dealing with forms in Yii:

   1. Create a model class representing the data fields to be collected;
   1. Create a controller action with code that responds to form submission.
   1. Create a form in the view script file associated with the controller action.

In the next subsections, we describe each of these steps in detail.

<div class="revision">$Id$</div>