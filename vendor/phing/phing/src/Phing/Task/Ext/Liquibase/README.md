# [READ-ONLY] task-liquibase

The LiquibaseTask is a generic task for liquibase commands that don't require extra command parameters. You can run commands like updateSQL, validate or updateTestingRollback with this task but not rollbackToDateSQL since it requires a date parameter after the command.

This is a read-only split of https://github.com/phingofficial/phing/tree/main/src/Phing/Task/Ext/Liquibase.

Please [report issues](https://github.com/phingofficial/phing/issues) and
[send Pull Requests](https://github.com/phingofficial/phing/pulls)
in the [main Phing repository](https://github.com/phingofficial/phing).
