概述
========

在开发中扩展Yii是一个很常见的行为.例如,当你写一个新的控制器时,你通过继承 [CController] 类扩展了
Yii;当你编写一个新的组件时,你正在继承 [CWidget] 或者一个已存在的组件类.如果扩展代码是由第三方开发者为了复用而设计的,我们则称之为 *extension(扩展)*.

一个扩展通常是为了一个单一的目的服务的.在 Yii 中,他可以按照如下分类:

 * [应用的部件](/doc/guide/basics.application#application-component)
 * [组件](/doc/guide/basics.view#widget)
 * [控制器](/doc/guide/basics.controller)
 * [动作](/doc/guide/basics.controller#action)
 * [过滤器](/doc/guide/basics.controller#filter)
 * [控制台命令](/doc/guide/topics.console)
 * 校验器: 校验器是一个继承自 [CValidator] 类的部件.
 * 辅助器: 辅助器是一个只具有静态方法的类.它类似于使用类名作为命名空间的全局函数.
 * 模块: 模块是一个有着若干个类文件和相应特长文件的包.一个模块通常更高级,比一个单一的部件具备更先进的功能.例如我们可以拥有一个具备整套用户管理功能的模块.

扩展也可以是不属于上述分类中的任何一个的部件.事实上,Yii 是设计的很谨慎,以至于几乎它的每段代码都可以被扩展和订制以适用于特定需求.

<div class="revision">$Id: extension.overview.txt 235 2009-03-16 10:07:50Z qiang.xue & 译: thaiki $</div>