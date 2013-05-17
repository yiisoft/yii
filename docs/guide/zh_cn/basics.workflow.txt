开发流程
====================

介绍过 Yii 中的基本概念之后，我们现在讲解使用 Yii 开发Web应用时的一般开发流程。
此处的开发流程假设我们已经完成了对应用的需求分析和必要的设计分析。

   1. 创建目录结构骨架。[创建第一个Web应用](/doc/guide/quickstart.first-app) 中讲到的
`yiic` 工具可以快速实现此步骤。

   2. 配置此 [应用](/doc/guide/basics.application)。这是通过修改应用配置文件实现的。
此步骤可能也需要编写一些应用组件（例如用户组件）。

   3. 为所管理的每个类型的数据创建一个 [模型](/doc/guide/basics.model) 类。
[Creating First Yii Application](doc/guide/quickstart.first-app#implementing-crud-operations)
和 [Automatic Code Generation](doc/guide/topics.gii) 中讲述的 `Gii` 工具可以用于快速为每个数据表创建
[active record](/doc/guide/database.ar) 类。

   4.为每个类型的用户请求 创建一个 [控制器](/doc/guide/basics.controller) 类。
具体如何对用户请求归类要看实际需求。总体来说，如果一个模型类需要被用户访问，他就应该有一个相应的控制器类。
`Gii` 工具也可以自动实现这一步骤。

   5. 实现 [动作](/doc/guide/basics.controller#action) 和他们相应的 [视图](/doc/guide/basics.view)。
这是真正所需要做的工作。

   6. 在控制器类中配置必要的动作 [过滤器](/doc/guide/basics.controller#filter)。

   7. 如果需要主题功能，创建 [主题](/doc/guide/topics.theming) 。

   8. 如果需要 [国际化（I18N）](/doc/guide/topics.i18n) ，创建翻译信息。

   9. 对可缓存的数据点和视图点应用适当的 [缓存](/doc/guide/caching.overview) 技术。

   10. 最终 [调整](/doc/guide/topics.performance) 与部署。

上述的每个步骤中，可能需要创建并执行测试用例。

<div class="revision">$Id: basics.workflow.txt 2388 2010-08-30 22:56:26Z alexander.makarow $</div>