从版本 1.0 升级到 1.1
=================================

与 Model Scenarios 相关的改变
------------------------------------

- 删除了 CModel::safeAttributes()。安全属性被定义为由 CModel::rules() 为特定场景指定的规则来验证。

- 改变了 CModel::validate(), CModel::beforeValidate() 和 CModel::afterValidate()。 CModel::setAttributes()， CModel::getSafeAttributeNames() 参数 'scenario' 被删除。你应当得到和设置模型场景，通过 CModel::scenario。

- 改变了 CModel::getValidators() 并删除了 CModel::getValidatorsForAttribute()。CModel::getValidators() 现在只返回适用于模型指定场景的验证器。

- 改变了 CModel::isAttributeRequired() 和 CModel::getValidatorsForAttribute()。scenario 参数被删除。而是使用模型的 scenario 属性。

- 删除了 CHtml::scenario。 CHtml 将使用模型的scenario 属性。


与 Eager Loading for Relational Active Record 相关的改变 
---------------------------------------------------------------

- 默认的， 一条 JOIN 语句将被生成并为 eager 载入涉及的所有关联执行。若主表有它的 `LIMIT` 或  `OFFSET` 查询选项，它将被单独首先查询，然后跟上取回其所有关联对象的另外的 SQL 。在版本 1.0.x 之前，默认的行为是，若一个 eager 载入涉及到 `N` 个 `HAS_MANY` or `MANY_MANY` 关联，将有 `N+1`  个 SQL 语句。

与在 Relational Active Record 中表别名相关的改变 
------------------------------------------------------------

- 现在一个关联表的默认别名和对应的关联的名字相同。 在版本 1.0.x 之前，默认情况下 Yii 将自动为每个关联表生成一个表别名，我们必须使用前缀 `??.` 来指向自动生成的别名。

- 在 AR 查询中的主表的别名确定为 `t`。在之前的版本 1.0.x，它和表的名字相同。This will cause existing AR query code to break if they explicity specify column prefixes using the table name. 解决办法是替换这些前缀为 `t.`。


与 Tabular 输入相关的改变
----------------------------------

- 对于属性名字，使用 `Field[$i]` 不再是有效的，它们应当类似于 `[$i]Field`，这是为了支持数组类型的字段 (例如 `[$i]Field[$index]`)。

其他改变
-------------

- [CActiveRecord] 构造器的签名被改变。第一个参数(属性列表) 被删除。

<div class="revision">$Id: upgrade.txt 2305 2010-08-06 10:27:11Z alexander.makarow $</div>