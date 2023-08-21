# [READ-ONLY] task-apigen

Task for ApiGen, a tool for creating professional API documentation from PHP source code.

This is a read-only split of https://github.com/phingofficial/phing/tree/main/src/Phing/Task/Ext/ApiGen.

Please [report issues](https://github.com/phingofficial/phing/issues) and
[send Pull Requests](https://github.com/phingofficial/phing/pulls)
in the [main Phing repository](https://github.com/phingofficial/phing).

## Attributes

| Name | Type | Description | Default | Required |
|---|---|---|---|---|
| executable | String  | ApiGen executable name.  | apigen  | No  |
| action | String | ApiGen action to be executed. | generate | No |
| config | String | Config file name. | n/a | Source and destination are required - either set explicitly or using a config file. Attribute values set explicitly have precedence over values from a config file. |
| source | String | List of source files or directories. | n/a |
| destination | String | Destination directory. | n/a |
| exclude | String | List of masks (case sensitive) to exclude files or directories from processing. | n/a | No |
| skipdocpath | String | List of masks (case sensitive) to exclude elements from documentation generating. | n/a | No |
| charset | String | Character set of source files. | UTF-8 | No |
| main | String | Main project name prefix. | n/a | No |
| title | String | Title of generated documentation. | n/a | No |
| baseurl | String | Documentation base URL. | n/a | No |
| googlecseid | String | Google Custom Search ID. | n/a | No |
| googlecselabel | String | Google Custom Search label. | n/a | No |
| googleanalytics | String | Google Analytics tracking code. | n/a | No |
| templateconfig | String | Template config file name. | n/a | If not set the default template is used. |
| templatetheme | String | Template theme file name. | n/a | If not set the default template is used. |
| accesslevels | String | Element access levels. Documentation only for methods and properties with the given access level will be generated. | public, protected | No |
| internal | Boolean | Whether to generate documentation for elements marked as internal and internal documentation parts or not. | No | No |
| php | Boolean | Whether to generate documentation for PHP internal classes or not. | Yes | No |
| tree | Boolean | Whether to generate tree view of classes, interfaces, traits and exceptions or not. | Yes | No |
| deprecated | Boolean | Whether to generate documentation for deprecated elements or not. | No | No |
| todo | Boolean | Whether to generate documentation of tasks or not. | No | No |
| sourcecode | Boolean | Whether to generate highlighted source code files or not. | Yes | No |
| download | Boolean | Whether to generate a link to download documentation as a ZIP archive or not. | No | No |
| debug | Boolean | Whether to enable the debug mode or not. | No | No

## Example
```xml
<apigen
  source="classes"
  destination="api"
  exclude="*/tests/*"
  title="My Project API Documentation"
  deprecated="true"
  todo="true"/>
```
