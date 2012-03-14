Upgrading from Version 1.0 to 1.1
=================================

Changes Related with Model Scenarios
------------------------------------

- Removed CModel::safeAttributes(). Safe attributes are now defined to
be those that are being validated by some rules as defined in CModel::rules()
for the particular scenario.

- Changed CModel::validate(), CModel::beforeValidate() and CModel::afterValidate().
CModel::setAttributes(), CModel::getSafeAttributeNames()
The 'scenario' parameter is removed. You should get and set the model scenario
via CModel::scenario.

- Changed CModel::getValidators() and removed CModel::getValidatorsForAttribute().
CModel::getValidators() now only returns validators applicable to the scenario
as specified by the model's scenario property.

- Changed CModel::isAttributeRequired() and CModel::getValidatorsForAttribute().
The scenario parameter is removed. The model's scenario property will be
used, instead.

- Removed CHtml::scenario. CHtml will use the model's scenario property instead.


Changes Related with Eager Loading for Relational Active Record
---------------------------------------------------------------

- By default, a single JOIN statement will be generated and executed
for all relations involved in the eager loading. If the primary table
has its `LIMIT` or `OFFSET` query option set, it will be queried alone
first, followed by another SQL statement that brings back all its related
objects. Previously in version 1.0.x, the default behavior is that
there will be `N+1` SQL statements if an eager loading involves
`N` `HAS_MANY` or `MANY_MANY` relations.

Changes Related with Table Alias in Relational Active Record
------------------------------------------------------------

- The default alias for a relational table is now the same as the corresponding
relation name. Previously in version 1.0.x, by default Yii would automatically
generate a table alias for each relational table, and we had to use the prefix
`??.` to refer to this automatically generated alias.

- The alias name for the primary table in an AR query is fixed to be `t`.
Previsouly in version 1.0.x, it was the same as the table name. This will cause
existing AR query code to break if they explicity specify column prefixes using
the table name. The solution is to replace these prefixes with 't.'.


Changes Related with Tabular Input
----------------------------------

- For attribute names, using `Field[$i]` is not valid anymore, they should look
like `[$i]Field` in order to support array-typed fields (e.g. `[$i]Field[$index]`).

Other Changes
-------------

- The signature of the [CActiveRecord] constructor is changed. The first parameter
(list of attributes) is removed.

<div class="revision">$Id$</div>