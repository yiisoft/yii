Development Workflow
====================

Having described the fundamental concepts of Yii, we show the common
workflow for developing a web application using Yii. The workflow assumes
that we have done the requirement analysis as well as the necessary design
analysis for the application.

   1. Create the skeleton directory structure. The `yiic` tool described in
[Creating First Yii Application](/doc/guide/quickstart.first-app) can be
used to speed up this step.

   2. Configure the [application](/doc/guide/basics.application). This is
done by modifying the application configuration file. This step may also
require writing some application components (e.g. the user component).

   3. Create a [model](/doc/guide/basics.model) class for each type of data
to be managed. The `Gii` tool described in
[Creating First Yii Application](/doc/guide/quickstart.first-app#implementing-crud-operations)
and in [Automatic Code Generation](/doc/guide/topics.gii) can be used to
automatically generate the [active record](/doc/guide/database.ar) class
for each interested database table.

   4. Create a [controller](/doc/guide/basics.controller) class for each
type of user requests. How to classify user requests depends on the actual
requirement. In general, if a model class needs to be accessed by users, it
should have a corresponding controller class. The `Gii` tool can automate
this step, too.

   5. Implement [actions](/doc/guide/basics.controller#action) and their
corresponding [views](/doc/guide/basics.view). This is where the real work
needs to be done.

   6. Configure necessary action
[filters](/doc/guide/basics.controller#filter) in controller classes.

   7. Create [themes](/doc/guide/topics.theming) if the theming feature is
required.

   8. Create translated messages if
[internationalization](/doc/guide/topics.i18n) is required.

   9. Spot data and views that can be cached and apply appropriate
[caching](/doc/guide/caching.overview) techniques.

   10. Final [tune up](/doc/guide/topics.performance) and deployment.

For each of the above steps, test cases may need to be created and
performed.

<div class="revision">$Id$</div>
