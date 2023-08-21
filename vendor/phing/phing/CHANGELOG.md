P     H     I     N     G
=========================


May 9, 2023 - Phing 3.0.0 rc5
-----------------------------

The following issues and pull requests were closed in this release:

* Remove phpcpd and phploc tasks [\#1708](https://github.com/phingofficial/phing/issues/1708)
* Add all task-related require-dev dependencies to suggest section in composer.json [\#1704](https://github.com/phingofficial/phing/issues/1704)
* `phploc/phploc` dependency in `require` blocks upgrade to PHPUnit 10+ [\#1703](https://github.com/phingofficial/phing/issues/1703)
* Migrate to monorepo [\#1693](https://github.com/phingofficial/phing/issues/1693)
* Regression in XmlPropertyTask RC3 -> RC4 [\#1692](https://github.com/phingofficial/phing/issues/1692)
* Fix XmlPropertyTask [\#1691](https://github.com/phingofficial/phing/pull/1691)
* Document sanitizing of text in Append task [\#1673](https://github.com/phingofficial/phing/issues/1673)
* Roll-out `pharLocation` "hack" to other tasks [\#1663](https://github.com/phingofficial/phing/issues/1663)

Jan. 20, 2023 - Phing 3.0.0 rc4
-------------------------------

The following issues and pull requests were closed in this release:

* XML\RNG Validation Wierdness [\#1688](https://github.com/phingofficial/phing/issues/1688)
* Uncaught Error: Call to undefined method ReflectionUnionType::isBuiltin() [\#1686](https://github.com/phingofficial/phing/issues/1686)
* FilesystemIterator bug in PHP 8.2 [\#1685](https://github.com/phingofficial/phing/pull/1685)
* TstampTask with ICU syntax [\#1683](https://github.com/phingofficial/phing/pull/1683)
* TstampTask impacted by deprecation [\#1682](https://github.com/phingofficial/phing/issues/1682)
* Fix wrong boolean handling [\#1680](https://github.com/phingofficial/phing/pull/1680)
* Update to 'symfony/yaml ^6' [\#1679](https://github.com/phingofficial/phing/issues/1679)
* Update VisualizerTask tests [\#1676](https://github.com/phingofficial/phing/pull/1676)
* Add support for PHP 8.1 [\#1674](https://github.com/phingofficial/phing/pull/1674)
* fixing DirSet toString returning files [\#1670](https://github.com/phingofficial/phing/pull/1670)
* DirSet toString returns files [\#1669](https://github.com/phingofficial/phing/issues/1669)
* Remove deprecated `command` attribute from ExecTask [\#1662](https://github.com/phingofficial/phing/issues/1662)
* Symfony console 6 support [\#1659](https://github.com/phingofficial/phing/pull/1659)
* Enable symfony 6 support [\#1658](https://github.com/phingofficial/phing/pull/1658)
* Missing php namespace in phpunit-noframes.xsl for phing-3.0.0-RC3.phar release [\#1656](https://github.com/phingofficial/phing/issues/1656)
* Type hasfreespace condition [\#1653](https://github.com/phingofficial/phing/pull/1653)
* PHP 8.1 compatibility [\#1652](https://github.com/phingofficial/phing/pull/1652)
* Update PHP requirement in Phar manifest [\#1650](https://github.com/phingofficial/phing/pull/1650)
* Align usage [\#1648](https://github.com/phingofficial/phing/pull/1648)
* Upgrading filesize [\#1644](https://github.com/phingofficial/phing/pull/1644)
* Add ExecTask suggestions [\#1643](https://github.com/phingofficial/phing/pull/1643)
* Fix PHP 8.1 tests and confirm compatibility [\#1642](https://github.com/phingofficial/phing/issues/1642)
* Finalize UPGRADING.md document [\#1640](https://github.com/phingofficial/phing/issues/1640)
* bump version phing/task-jshint in composer.lock  [\#1639](https://github.com/phingofficial/phing/issues/1639)
* Suggestions for ExecTask [\#1638](https://github.com/phingofficial/phing/issues/1638)
* Fix open task test [\#1637](https://github.com/phingofficial/phing/pull/1637)
* Spawn open task [\#1636](https://github.com/phingofficial/phing/pull/1636)
* Add tests for StringHelper [\#1603](https://github.com/phingofficial/phing/pull/1603)
* Fix XmlLintTask error handler [\#1599](https://github.com/phingofficial/phing/pull/1599)

Sep. 9, 2021 - Phing 3.0.0 rc3
------------------------------

The following issues and pull requests were closed in this release:

* [stopwatch] Added test and cleanup. [\#1635](https://github.com/phingofficial/phing/pull/1635)
* [pdosqlexec] Using traits [\#1634](https://github.com/phingofficial/phing/pull/1634)
* [pdosqlexec] Added failOnConnectionError / Removed obsolete caching attribute [\#1632](https://github.com/phingofficial/phing/pull/1632)
* [pdosqlexec] Added more attributes. [\#1631](https://github.com/phingofficial/phing/pull/1631)
* [delete] Removed duplicated log entry [\#1630](https://github.com/phingofficial/phing/pull/1630)
* [mkdir] Fixed PHP 8 compat [\#1629](https://github.com/phingofficial/phing/pull/1629)
* [pdosqlexec] Fixed PHP 8 compat, Code Cleanup and tests [\#1628](https://github.com/phingofficial/phing/pull/1628)
* Error with MkdirTask [\#1619](https://github.com/phingofficial/phing/issues/1619)
* Remove the default value before the required parameter. [\#1618](https://github.com/phingofficial/phing/pull/1618)
* Add OpenTask [\#1617](https://github.com/phingofficial/phing/pull/1617)
* Add support for formatter nested tag to the phpcs task [\#1614](https://github.com/phingofficial/phing/pull/1614)
* bump version phing/task-svn in composer.lock [\#1613](https://github.com/phingofficial/phing/issues/1613)

Aug. 31, 2021 - Phing 2.17.0
----------------------------

This patch release fixes some PHP 8.0 compatibility issues.

Jun. 28, 2021 - Phing 3.0.0 rc2
-------------------------------

The following issues and pull requests were closed in this release:

* Add support for setting standard, outfile and format to phpcs task [\#1612](https://github.com/phingofficial/phing/pull/1612)
* Update SizeSelector schema [\#1611](https://github.com/phingofficial/phing/pull/1611)
* [core] Updated dependencies [\#1593](https://github.com/phingofficial/phing/pull/1593)
* [core] Added additional tests [\#1592](https://github.com/phingofficial/phing/pull/1592)
* [XmlLogger] cleanup [\#1591](https://github.com/phingofficial/phing/pull/1591)
* [core] Added openssl and phar.readonly to pipeline [\#1590](https://github.com/phingofficial/phing/pull/1590)
* [PharPackageTask] Added attributes to test build [\#1589](https://github.com/phingofficial/phing/pull/1589)
* [core] Fixed ExtendedFileStream register wrapper [\#1588](https://github.com/phingofficial/phing/pull/1588)
* [core] Cleanup some tests. [\#1587](https://github.com/phingofficial/phing/pull/1587)
* [Location] Added test [\#1586](https://github.com/phingofficial/phing/pull/1586)
* Refactoring how Phing handles boolean strings [\#1584](https://github.com/phingofficial/phing/pull/1584)
* Use constant for buildfile name [\#1583](https://github.com/phingofficial/phing/pull/1583)
* Scrutinizer issue fixes [\#1581](https://github.com/phingofficial/phing/pull/1581)
* Scrutinizer issue fixes [\#1580](https://github.com/phingofficial/phing/pull/1580)
* Scrutinizer issue fixes [\#1579](https://github.com/phingofficial/phing/pull/1579)
* Scrutinizer issue fixes [\#1578](https://github.com/phingofficial/phing/pull/1578)
* Scrutinizer issue fixes [\#1577](https://github.com/phingofficial/phing/pull/1577)
* [core] Scrutinizer issue fixes [\#1576](https://github.com/phingofficial/phing/pull/1576)
* [XmlLintTask] Added error logs. [\#1575](https://github.com/phingofficial/phing/pull/1575)
* [core] Fixed slack url and added security advisor dependency [\#1574](https://github.com/phingofficial/phing/pull/1574)
* [core] Updated Deps - added funding to composer [\#1573](https://github.com/phingofficial/phing/pull/1573)
* [core] Updated deps [\#1572](https://github.com/phingofficial/phing/pull/1572)
* [core] Code cleanup on test files [\#1570](https://github.com/phingofficial/phing/pull/1570)
* php-cs-fixer fixed covers [\#1568](https://github.com/phingofficial/phing/pull/1568)
* [core] php-cs-fixer run [\#1566](https://github.com/phingofficial/phing/pull/1566)
* [core] Moved test classes to own namespace `Phing\Test\` [\#1565](https://github.com/phingofficial/phing/pull/1565)
* [FileUtils] split up long method [\#1564](https://github.com/phingofficial/phing/pull/1564)
* [core] PHP compat issues [\#1563](https://github.com/phingofficial/phing/pull/1563)
* Add comment in test to prevent php8.1 compat issue [\#1562](https://github.com/phingofficial/phing/pull/1562)
* Update documentation links [\#1561](https://github.com/phingofficial/phing/pull/1561)
* [condition] Added phpversion condition [\#1560](https://github.com/phingofficial/phing/pull/1560)
* [core] Removed unused exception classes [\#1559](https://github.com/phingofficial/phing/pull/1559)
* [core]Moved Timer function to DefaultClock [\#1558](https://github.com/phingofficial/phing/pull/1558)
* [Logger] Fixed StatisticsReport [\#1557](https://github.com/phingofficial/phing/pull/1557)
* [core] Removed deprecated NullPointerException.php [\#1556](https://github.com/phingofficial/phing/pull/1556)
* Updated PEAR\Exception -> composer update [\#1555](https://github.com/phingofficial/phing/pull/1555)
* Add days and hours to `DefaultLogger::formatTime` method [\#1554](https://github.com/phingofficial/phing/pull/1554)
* Update report bugs to use issues link [\#1551](https://github.com/phingofficial/phing/pull/1551)
* Remove currentTimeMillis [\#1545](https://github.com/phingofficial/phing/pull/1545)
* Fix "depends on" in target list [\#1544](https://github.com/phingofficial/phing/pull/1544)
* [ReflexiveTask] Call to a member function isDirectory() on null [\#1540](https://github.com/phingofficial/phing/issues/1540)

Feb. 17, 2021 - Phing 3.0.0 rc1
-------------------------------

The following issues and pull requests were closed in this release:

 * [ReflexiveTask] Added exception for missing filterchain [\#1538](https://github.com/phingofficial/phing/pull/1538)
 * [ReflexiveTask] Fixed behavior, if file attribute points to a directory. [\#1537](https://github.com/phingofficial/phing/pull/1537)
 * [PropertySelector] Fixed group selection [\#1536](https://github.com/phingofficial/phing/pull/1536)
 * [core] Fixed deprecation warnings - PHP ^8.1 [\#1535](https://github.com/phingofficial/phing/pull/1535)
 * [TryCatchTask] Rectify the logic of the "finally" clause [\#1534](https://github.com/phingofficial/phing/pull/1534)
 * [PhingTask] Fixed dot notation [\#1533](https://github.com/phingofficial/phing/pull/1533)
 * Fix PhpEvalTask bugs with functions taking scalar and multidimensional array params [\#1531](https://github.com/phingofficial/phing/pull/1531)
 * Moved aws related tasks to own repo [\#1530](https://github.com/phingofficial/phing/pull/1530)
 * Moved SassTask to own repo [\#1529](https://github.com/phingofficial/phing/pull/1529)
 * Moved PhpUnitTask to own repo [\#1528](https://github.com/phingofficial/phing/pull/1528)
 * Moved SonarTask to own repo [\#1527](https://github.com/phingofficial/phing/pull/1527)
 * Moved analyzer tasks to own repo [\#1526](https://github.com/phingofficial/phing/pull/1526)
 * Moved http tasks to own repo. [\#1523](https://github.com/phingofficial/phing/pull/1523)
 * Move pdo tasks and path-as-package task to system scope [\#1521](https://github.com/phingofficial/phing/pull/1521)
 * Moved svn tasks to own repo [\#1520](https://github.com/phingofficial/phing/pull/1520)
 * [core] Moved XmlPropertyTask and PharMetadata to system tasks. [\#1519](https://github.com/phingofficial/phing/pull/1519)
 * [core] Move archive tasks [\#1518](https://github.com/phingofficial/phing/pull/1518)
 * Moved DbdeployTask to own repo [\#1516](https://github.com/phingofficial/phing/pull/1516)
 * Rename "master" branch to "main" [\#1514](https://github.com/phingofficial/phing/issues/1514)
 * Moved VisualizerTask to own repo [\#1513](https://github.com/phingofficial/phing/pull/1513)
 * Moved inifile task to own repo [\#1512](https://github.com/phingofficial/phing/pull/1512)
 * [SassTask] [core] - Fixed Filesystem::listContents for numeric filenames. [\#1511](https://github.com/phingofficial/phing/pull/1511)
 * Removed unused imports [\#1510](https://github.com/phingofficial/phing/pull/1510)
 * Use Phing\Type\Element\.*Aware traits [\#1508](https://github.com/phingofficial/phing/pull/1508)
 * Finalize namespace-ification [\#1506](https://github.com/phingofficial/phing/pull/1506)
 * Moved git tasks to own repo [\#1505](https://github.com/phingofficial/phing/pull/1505)
 * Moved HG tasks to own repo [\#1504](https://github.com/phingofficial/phing/pull/1504)
 * Fixed windows line ending issue [\#1500](https://github.com/phingofficial/phing/pull/1500)
 * Fixed line ending issue on windows [\#1499](https://github.com/phingofficial/phing/pull/1499)
 * excludesfile example [\#1498](https://github.com/phingofficial/phing/issues/1498)
 * Updated composer dependencies [\#1496](https://github.com/phingofficial/phing/pull/1496)
 * Added ModifiedSelector. [\#1487](https://github.com/phingofficial/phing/pull/1487)
 * [PHPUnitTask] Removed PHPUnitTestRunner7 [\#1486](https://github.com/phingofficial/phing/pull/1486)
 * [Pear*] Removed unused test xml file. [\#1485](https://github.com/phingofficial/phing/pull/1485)
 * [phploc] Fixed test case. [\#1484](https://github.com/phingofficial/phing/pull/1484)
 * [phploc] Json formatter not tested [\#1483](https://github.com/phingofficial/phing/issues/1483)
 * Size helper [\#1482](https://github.com/phingofficial/phing/pull/1482)
 * [PatternSet] Simplified __toString method. [\#1481](https://github.com/phingofficial/phing/pull/1481)
 * Standardize file size units [\#1480](https://github.com/phingofficial/phing/issues/1480)
 * [core] Fixed codecoverage in action with latest ubuntu [\#1478](https://github.com/phingofficial/phing/pull/1478)
 * Show suggestion on unknown target [\#1477](https://github.com/phingofficial/phing/pull/1477)
 * [BindTargets] Added BindTargets Task [\#1476](https://github.com/phingofficial/phing/pull/1476)
 * Changed build trigger handling [\#1474](https://github.com/phingofficial/phing/pull/1474)
 * [DateSelector] Add tests and correct behavior [\#1473](https://github.com/phingofficial/phing/pull/1473)
 * [core] Fixed delta in TimerTest.php [\#1471](https://github.com/phingofficial/phing/pull/1471)
 * [core] Move internal build steps from travis to github [\#1470](https://github.com/phingofficial/phing/pull/1470)
 * Updated Dependencies [\#1469](https://github.com/phingofficial/phing/pull/1469)
 * Changed arg passed to phploc [\#1457](https://github.com/phingofficial/phing/pull/1457)
 * Add tests and correct TouchTask behavior [\#1456](https://github.com/phingofficial/phing/pull/1456)
 * [PHPUnitTask] Added haltondefect argument [\#1455](https://github.com/phingofficial/phing/pull/1455)
 * [TouchTask] Test doesn't check that files were created with the correct timestamp [\#1454](https://github.com/phingofficial/phing/issues/1454)
 * [TouchTask] millis attribute leads to invalid dates [\#1453](https://github.com/phingofficial/phing/issues/1453)
 * [TouchTask] allows invalid datetime values to be specified [\#1452](https://github.com/phingofficial/phing/issues/1452)
 * [PHPUnitTask] Fixed processIsolation setting [\#1451](https://github.com/phingofficial/phing/pull/1451)
 * [PHPUnitTask] renamed formatter [\#1448](https://github.com/phingofficial/phing/pull/1448)
 * [core] Removed unused excludes in test build. [\#1447](https://github.com/phingofficial/phing/pull/1447)
 * [core] Work on PHP 8 [\#1445](https://github.com/phingofficial/phing/pull/1445)
 * [DeleteTask] Fixed that only variables can be passed by reference. [\#1444](https://github.com/phingofficial/phing/pull/1444)
 * [PHPUnitTask] Removed includes [\#1443](https://github.com/phingofficial/phing/pull/1443)
 * [core] Fixed tests [\#1442](https://github.com/phingofficial/phing/pull/1442)
 * [core] Added PHP 8.0 and 8.1 snapshot to travis [\#1441](https://github.com/phingofficial/phing/pull/1441)
 * Fix deprecated ReflectionParameter::getClass() [\#1440](https://github.com/phingofficial/phing/pull/1440)
 * [PlainPHPUnitResultFormatter] Added risky counter [\#1439](https://github.com/phingofficial/phing/pull/1439)
 * [PHPUnitResultFormatter] Added risky test counter to summary [\#1438](https://github.com/phingofficial/phing/pull/1438)
 * [CoverageMergerTask] Fixed code coverage handling [\#1437](https://github.com/phingofficial/phing/pull/1437)
 * Add new optional clover-html formatter. [\#1436](https://github.com/phingofficial/phing/pull/1436)
 * [PHPUnitResultFormatter] Added warning and risky counter [\#1435](https://github.com/phingofficial/phing/pull/1435)
 * [PHPUnitTask] Added failure handling for risky and warning tests [\#1434](https://github.com/phingofficial/phing/pull/1434)
 * [PHPUnitTask] Added missing halton* methods. [\#1433](https://github.com/phingofficial/phing/pull/1433)
 * Added coverage-* related test setup [\#1432](https://github.com/phingofficial/phing/pull/1432)
 * Make use of PHPUnit 9 code coverage and reporting [\#1431](https://github.com/phingofficial/phing/pull/1431)
 * Make better use of PHPUnit 9 code coverage and reporting [\#1430](https://github.com/phingofficial/phing/issues/1430)
 * [PHPUnitReportTask] Fixed windows detection [\#1429](https://github.com/phingofficial/phing/pull/1429)
 * Delete unused. [\#1428](https://github.com/phingofficial/phing/pull/1428)
 * ComposerTask tests don't need Composer on the path [\#1427](https://github.com/phingofficial/phing/pull/1427)
 * ComposerTask test failing if Composer isn't on the path [\#1426](https://github.com/phingofficial/phing/issues/1426)
 * SonarConfigurationFileParser tests fail on cygwin (Windows) [\#1425](https://github.com/phingofficial/phing/pull/1425)
 * Fix ini parser must ignore ini sections [\#1421](https://github.com/phingofficial/phing/pull/1421)
 * Fixed env handling for windows [\#1419](https://github.com/phingofficial/phing/pull/1419)
 * Environment argument for ExecTask is broken for windows [\#1417](https://github.com/phingofficial/phing/issues/1417)
 * Move composer vaidation to action [\#1415](https://github.com/phingofficial/phing/pull/1415)
 * Added GITHub action workflow. [\#1414](https://github.com/phingofficial/phing/pull/1414)
 * Fixed Windows codepage setting for utf8 beta. [\#1413](https://github.com/phingofficial/phing/pull/1413)
 * Updated dependencies [\#1412](https://github.com/phingofficial/phing/pull/1412)
 * Windows & UTF-8 [\#1399](https://github.com/phingofficial/phing/issues/1399)
 * Fix Support for Nested Exec Env Tag - Fixes #1395 [\#1396](https://github.com/phingofficial/phing/pull/1396)
 * ENV SubTask Not Working as Expected [\#1395](https://github.com/phingofficial/phing/issues/1395)
 * Added new version of FtpDeloyTask [\#1394](https://github.com/phingofficial/phing/pull/1394)
 * Variable properties which could not expanded should be null. [\#1393](https://github.com/phingofficial/phing/pull/1393)
 * Error resolving proxy [\#1391](https://github.com/phingofficial/phing/issues/1391)
 * Fix visualizer bug [\#1390](https://github.com/phingofficial/phing/pull/1390)
 * [HttpRequest] Fixed verbose mode. [\#1389](https://github.com/phingofficial/phing/pull/1389)
 * [phploc] Fixed broken task for PHP >= 7.3 [\#1387](https://github.com/phingofficial/phing/pull/1387)
 * Downgraded guzzle to 6.5 [\#1386](https://github.com/phingofficial/phing/pull/1386)
 * httpget broken by guzzle update [\#1385](https://github.com/phingofficial/phing/issues/1385)
 * Could not create task/type: 'scp'. Make sure that this class has been declared using taskdef / typedef. (3.0.0-alpha4) [\#1372](https://github.com/phingofficial/phing/issues/1372)
 * VisualizerTask bug [\#1388](https://github.com/phingofficial/phing/issues/1388)
 * Reliance on PEAR packages despite being removed from PEAR itself [\#1370](https://github.com/phingofficial/phing/issues/1370)
 * [SubPhing] Fail on error should also be passed to the phing task. [\#1361](https://github.com/phingofficial/phing/pull/1361)
 * Cleanup test build script [\#1332](https://github.com/phingofficial/phing/pull/1332)
 * Added missing Target::dependsOn implementation [\#1303](https://github.com/phingofficial/phing/pull/1303)
 * Declaration of case-insensitive constants is deprecated - replace Net_FTP [\#1224](https://github.com/phingofficial/phing/issues/1224)

Jan. 29, 2021 - Phing 2.16.4
----------------------------

This patch release fixes some PHP 8.0 compatibility issues.

Jul. 4, 2020 - Phing 3.0.0 alpha 4
----------------------------------

The following issues and pull requests were closed in this release:

 * [PatternSet] Added missing test. [\#1350](https://github.com/phingofficial/phing/pull/1350)
 * Phpcstask fileset support [\#1349](https://github.com/phingofficial/phing/pull/1349)
 * Removed PhpCodeSnifferTask [\#1346](https://github.com/phingofficial/phing/pull/1346)
 * [test/build.xml] Removed adhoc tasks and use bootstrap [\#1336](https://github.com/phingofficial/phing/pull/1336)
 * Fixed condition [\#1334](https://github.com/phingofficial/phing/pull/1334)
 * Added extension points [\#1324](https://github.com/phingofficial/phing/pull/1324)
 * Added augment reference task. [\#1323](https://github.com/phingofficial/phing/pull/1323)
 * Added ClassConstants filter [\#1322](https://github.com/phingofficial/phing/pull/1322)
 * Removed ansible support in favor of docker [\#1321](https://github.com/phingofficial/phing/pull/1321)
 * Moved Zend Server Development Tools Tasks to own repo [\#1320](https://github.com/phingofficial/phing/pull/1320)
 * [PathConvert] Fixed validation on attributes. [\#1319](https://github.com/phingofficial/phing/pull/1319)
 * [FileUtils] Fixed file separator/pathSeparator as not always set. [\#1318](https://github.com/phingofficial/phing/pull/1318)
 * Fixed phpunit warnings [\#1317](https://github.com/phingofficial/phing/pull/1317)
 * [subphing] Added bulk project execution task. [\#1316](https://github.com/phingofficial/phing/pull/1316)
 * [build.xml] Fixed deprecated warning. [\#1315](https://github.com/phingofficial/phing/pull/1315)
 * [TruncateTask] Simplified new file creation. [\#1314](https://github.com/phingofficial/phing/pull/1314)
 * [AdhocTaskdefTask] Fixed is subclass of task test. [\#1313](https://github.com/phingofficial/phing/pull/1313)
 * [Target] Added location support. [\#1312](https://github.com/phingofficial/phing/pull/1312)
 * [Phing] Used finally to simplify exc handling [\#1311](https://github.com/phingofficial/phing/pull/1311)
 * [Phing] Removed not needed method [\#1310](https://github.com/phingofficial/phing/pull/1310)
 * [Phing] Removed deprecated setting of track_errors [\#1309](https://github.com/phingofficial/phing/pull/1309)
 * [Phing] Simplified os family condition [\#1308](https://github.com/phingofficial/phing/pull/1308)
 * [Phing] Removed php compat condition [\#1307](https://github.com/phingofficial/phing/pull/1307)
 * Moved JsHintTask to own repo [\#1306](https://github.com/phingofficial/phing/pull/1306)
 * [PhingTask] Fixed exception handling and condition [\#1305](https://github.com/phingofficial/phing/pull/1305)
 * Moved JsMinTask to own repo. [\#1304](https://github.com/phingofficial/phing/pull/1304)
 * [PhingTask] Fixed multi same property [\#1296](https://github.com/phingofficial/phing/pull/1296)
 * [PhingTask] Added native basedir support. [\#1295](https://github.com/phingofficial/phing/pull/1295)
 * [WIP] [PhingTask] Fixed possible infinity loop [\#1294](https://github.com/phingofficial/phing/pull/1294)
 * [ForeachTask] Cleanup code [\#1293](https://github.com/phingofficial/phing/pull/1293)
 * [PhingTask] Cleanup code. [\#1292](https://github.com/phingofficial/phing/pull/1292)
 * [PhingTask] Added output argument. [\#1291](https://github.com/phingofficial/phing/pull/1291)
 * [MonologListener] Fixed logging for warning [\#1290](https://github.com/phingofficial/phing/pull/1290)
 * [PropertyTask] reduce complexity by extract method [\#1287](https://github.com/phingofficial/phing/pull/1287)
 * Moved phpdoc task to own repository. [\#1286](https://github.com/phingofficial/phing/pull/1286)
 * [MoveTask] Added granularity support [\#1278](https://github.com/phingofficial/phing/pull/1278)
 * [TouchTask] fixed log output setting datetime [\#1277](https://github.com/phingofficial/phing/pull/1277)
 * [CopyTask] Added granularity support on LMT of src [\#1276](https://github.com/phingofficial/phing/pull/1276)
 * [FileUtils] Added granularity support [\#1275](https://github.com/phingofficial/phing/pull/1275)
 * [MoveTask] Added preservepermissions support. [\#1274](https://github.com/phingofficial/phing/pull/1274)
 * [PhingTask] Added some more general tests [\#1273](https://github.com/phingofficial/phing/pull/1273)
 * [PhingTask] Added override tests [\#1272](https://github.com/phingofficial/phing/pull/1272)
 * [PhingTask] Added ref no inheritance and path test [\#1271](https://github.com/phingofficial/phing/pull/1271)
 * [PhingTask] Added reference inheritance test [\#1270](https://github.com/phingofficial/phing/pull/1270)
 * [TruncateTask] fixed unit suffix on length/adjust [\#1269](https://github.com/phingofficial/phing/pull/1269)
 * [MoveTask] Fixed default overwrite behavior. [\#1268](https://github.com/phingofficial/phing/pull/1268)
 * Moved ssh tasks to own repo [\#1267](https://github.com/phingofficial/phing/pull/1267)
 * Moved zendcodeanalyser task to own repo [\#1266](https://github.com/phingofficial/phing/pull/1266)
 * Moved SmartyTask to own repository. [\#1265](https://github.com/phingofficial/phing/pull/1265)
 * [PhingTask] added inherit basedir tests [\#1264](https://github.com/phingofficial/phing/pull/1264)
 * [PhingTask] Added some more tests [\#1263](https://github.com/phingofficial/phing/pull/1263)
 * [PhingTask] Added test implementations [\#1262](https://github.com/phingofficial/phing/pull/1262)
 * [PhingTask] add xml test files [\#1261](https://github.com/phingofficial/phing/pull/1261)
 * [core] Removed not used assignment [\#1260](https://github.com/phingofficial/phing/pull/1260)
 * [Task] bind task to another task [\#1259](https://github.com/phingofficial/phing/pull/1259)
 * [PropertyHelper] Reduced complexity [\#1258](https://github.com/phingofficial/phing/pull/1258)
 * [Project] Added inherited properties getter. [\#1257](https://github.com/phingofficial/phing/pull/1257)
 * [TouchTask] Added mapping support. [\#1256](https://github.com/phingofficial/phing/pull/1256)
 * [ZipTask] fixed basedir [\#1255](https://github.com/phingofficial/phing/pull/1255)
 * [ExecTask] Fixed resolving env vars [\#1254](https://github.com/phingofficial/phing/pull/1254)
 * Using ExecTask with environment variables [\#1253](https://github.com/phingofficial/phing/issues/1253)
 * Moved ioncube tasks to own repo [\#1249](https://github.com/phingofficial/phing/pull/1249)
 * Moved PhkPackageTask to own repo. [\#1248](https://github.com/phingofficial/phing/pull/1248)
 * Simplify visualizer tests [\#1247](https://github.com/phingofficial/phing/pull/1247)
 * Moved FtpDeployTask to own repo [\#1246](https://github.com/phingofficial/phing/pull/1246)
 * VisualizerTask breaks test execution [\#1245](https://github.com/phingofficial/phing/issues/1245)
 * Provide --config-option switch for svn tasks [\#1244](https://github.com/phingofficial/phing/pull/1244)
 * [PhingCallTask] set target on callee [\#1243](https://github.com/phingofficial/phing/pull/1243)
 * ZipTask cannot create zip using basedir [\#1242](https://github.com/phingofficial/phing/issues/1242)
 * [PhingTask] Added full subproject handling. [\#1241](https://github.com/phingofficial/phing/pull/1241)
 * Fixed error, if error_get_last equals to null [\#1240](https://github.com/phingofficial/phing/pull/1240)
 * Added regression tests [\#1239](https://github.com/phingofficial/phing/pull/1239)
 * Added echoxml task [\#1238](https://github.com/phingofficial/phing/pull/1238)
 * Added support for creating custom attributes in the parsing phase. [\#1237](https://github.com/phingofficial/phing/pull/1237)
 * [DiagnosticsTask] Fixed composer warning. [\#1236](https://github.com/phingofficial/phing/pull/1236)
 * Moved coverage tasks to new repo [\#1230](https://github.com/phingofficial/phing/pull/1230)
 * Removed ExportPropertiesTask in favor of EchoProperties task [\#1229](https://github.com/phingofficial/phing/pull/1229)
 * Moved Liquibase Tasks to an ext repo. [\#1228](https://github.com/phingofficial/phing/pull/1228)
 * [PropertyTask] Added "required" attribute [\#1226](https://github.com/phingofficial/phing/pull/1226)
 * Added custom task/type support [\#1225](https://github.com/phingofficial/phing/pull/1225)
 * Fixed IsTrueCondition [\#1221](https://github.com/phingofficial/phing/pull/1221)
 * [PropertyTask] Added type hints [\#1218](https://github.com/phingofficial/phing/pull/1218)
 * [EchoTask] Fixed type handling. [\#1217](https://github.com/phingofficial/phing/pull/1217)
 * Removed memory limit from travis ini and some refactor [\#1216](https://github.com/phingofficial/phing/pull/1216)
 * Added posix permission selector [\#1209](https://github.com/phingofficial/phing/pull/1209)
 * Added multi line description support, … [\#1208](https://github.com/phingofficial/phing/pull/1208)
 * SassTask mangles the CLI command depending on attribute order [\#1206](https://github.com/phingofficial/phing/issues/1206)
 * Monolog listener [\#1204](https://github.com/phingofficial/phing/pull/1204)
 * Value "0" is impossible [\#1201](https://github.com/phingofficial/phing/issues/1201)
 * Incorrect type cast string-boolean [\#1200](https://github.com/phingofficial/phing/issues/1200)
 * Updated dependencies [\#1195](https://github.com/phingofficial/phing/pull/1195)
 * [WIP] Symfony 5 compat [\#1194](https://github.com/phingofficial/phing/pull/1194)
 * replace ignore-checks in tests [\#1193](https://github.com/phingofficial/phing/pull/1193)
 * replace @expectedException* anotation [\#1191](https://github.com/phingofficial/phing/pull/1191)
 * [FileList] Fixed iterator [\#1190](https://github.com/phingofficial/phing/pull/1190)
 * Removed unused lines of code. [\#1189](https://github.com/phingofficial/phing/pull/1189)
 * fix coding style for test files [\#1188](https://github.com/phingofficial/phing/pull/1188)
 * Allow symfony/* ^5.0 [\#1185](https://github.com/phingofficial/phing/issues/1185)
 * Fixed indention [\#1183](https://github.com/phingofficial/phing/pull/1183)
 * Move from PhingFile to SplFileObject - part 1 [\#1178](https://github.com/phingofficial/phing/pull/1178)
 * Fixed not called Phing::shutdown() [\#1177](https://github.com/phingofficial/phing/pull/1177)
 * Parameter unittests [\#1176](https://github.com/phingofficial/phing/pull/1176)
 * Update ComposerTask code & documentation [\#1175](https://github.com/phingofficial/phing/pull/1175)
 * fix coding style issues [\#1174](https://github.com/phingofficial/phing/pull/1174)
 * Added PrefixLines test [\#1168](https://github.com/phingofficial/phing/pull/1168)
 * Added SilentLoggerTest [\#1167](https://github.com/phingofficial/phing/pull/1167)
 * Create TimestampedLoggerTest [\#1166](https://github.com/phingofficial/phing/pull/1166)
 * Added test for StatisticsListener [\#1165](https://github.com/phingofficial/phing/pull/1165)
 * [DefaultLogger] added unit test for buildFinished [\#1164](https://github.com/phingofficial/phing/pull/1164)
 * CompserTask documentation [\#1163](https://github.com/phingofficial/phing/issues/1163)
 * PSR12 and Object Calisthenics [\#1161](https://github.com/phingofficial/phing/pull/1161)
 * get_magic_quotes_runtime() deprecated in 7.4 - replace HTTP_Request2 [\#1160](https://github.com/phingofficial/phing/issues/1160)
 * Added SleepTaskTest [\#1153](https://github.com/phingofficial/phing/pull/1153)
 * Fixed PSR12 related errors by phpcbf [\#1152](https://github.com/phingofficial/phing/pull/1152)
 * Add unit test for Description addText method [\#1151](https://github.com/phingofficial/phing/pull/1151)
 * Added bootstrap to scrutinizer config [\#1150](https://github.com/phingofficial/phing/pull/1150)
 * [PhingTest] Added test case for printUsage [\#1149](https://github.com/phingofficial/phing/pull/1149)
 * Fixed notice in JsonLogger. [\#1148](https://github.com/phingofficial/phing/pull/1148)
 * [DataTypeTest] Added missing license and property [\#1147](https://github.com/phingofficial/phing/pull/1147)
 * Datatype unit-tests [\#1146](https://github.com/phingofficial/phing/pull/1146)
 * [StatisticsListener] Fixed PHP Error [\#1145](https://github.com/phingofficial/phing/pull/1145)
 * The variable '$php_errormsg' is deprecated since PHP 7.2; Using error_get_last() instead [\#1144](https://github.com/phingofficial/phing/pull/1144)
 * Rename __import method [\#1143](https://github.com/phingofficial/phing/pull/1143)
 * StringReader should be an InputStreamReader [\#1141](https://github.com/phingofficial/phing/pull/1141)
 * PDOSQLExecTask constructor error [\#1138](https://github.com/phingofficial/phing/issues/1138)
 * update coding style [\#1137](https://github.com/phingofficial/phing/pull/1137)
 * add Build-Matrix [\#1136](https://github.com/phingofficial/phing/pull/1136)
 * FileHashTask should always generate a file. [\#1135](https://github.com/phingofficial/phing/pull/1135)
 * Added loglevel attribute to the phpcs task. [\#1134](https://github.com/phingofficial/phing/pull/1134)
 * Removed duplicated code. [\#1133](https://github.com/phingofficial/phing/pull/1133)
 * update dependencies [\#1132](https://github.com/phingofficial/phing/pull/1132)
 * Added ext-intl to appveyor.yml [\#1131](https://github.com/phingofficial/phing/pull/1131)
 * add editorconfig, update gitattributes [\#1130](https://github.com/phingofficial/phing/pull/1130)
 * [WIP] DirectoryScanner and AbstractFileSet improvements. [\#1034](https://github.com/phingofficial/phing/pull/1034)
 * Auto-discover custom tasks when installed through Composer [\#654](https://github.com/phingofficial/phing/issues/654)
 * MkdirTask behaves the same as "mkdir" Linux command and respects POSIX ACL [\#591](https://github.com/phingofficial/phing/pull/591)

Feb. 3, 2020 - Phing 2.16.3
---------------------------

This patch release fixes additional PHP 7.4 deprecation issues.

Jan. 3, 2020 - Phing 2.16.2
---------------------------

This patch release fixes the following issue:

* PHP-7.4: PHP Deprecated: Array and string offset access syntax with curly braces [\#1210](https://github.com/phingofficial/phing/issues/1210)
* Add symfony/yaml ^2.8 to restore PHP 5.4 compatibility [\#919](https://github.com/phingofficial/phing/issues/919)

Sep. 13, 2019 - Phing 3.0.0 alpha 3
-----------------------------------

The following issues and pull requests were closed in this release:

 * Fix some PHP 7.4 specific deprecations. [\#1127](https://github.com/phingofficial/phing/pull/1127)
 * Bump scssphp/scssphp from 1.0.2 to 1.0.3 [\#1126](https://github.com/phingofficial/phing/pull/1126)
 * Bump aws/aws-sdk-php from 3.108.2 to 3.110.7 [\#1125](https://github.com/phingofficial/phing/pull/1125)
 * Bump phpunit/phpunit from 7.5.14 to 7.5.15 [\#1124](https://github.com/phingofficial/phing/pull/1124)
 * Code cleanup [\#1122](https://github.com/phingofficial/phing/pull/1122)
 * database condition [\#1121](https://github.com/phingofficial/phing/pull/1121)
 * Added verbose log to mkdir if dir exists already. [\#1120](https://github.com/phingofficial/phing/pull/1120)
 * Get rid of useless code in PhingFile [\#1119](https://github.com/phingofficial/phing/pull/1119)
 * Reduced copy paste [\#1118](https://github.com/phingofficial/phing/pull/1118)
 * Removed redundant else branch. [\#1117](https://github.com/phingofficial/phing/pull/1117)
 * Fixed some inspections [\#1116](https://github.com/phingofficial/phing/pull/1116)
 * Fixed low deps issue [\#1115](https://github.com/phingofficial/phing/pull/1115)
 * Added circular reference check. [\#1114](https://github.com/phingofficial/phing/pull/1114)
 * Test suite fails for travis on php 7.1 with low deps [\#1113](https://github.com/phingofficial/phing/issues/1113)
 * Fixed some more inspections [\#1111](https://github.com/phingofficial/phing/pull/1111)
 * Fixed some more ci issues. [\#1110](https://github.com/phingofficial/phing/pull/1110)
 * Fixed scrutinizer issue [\#1109](https://github.com/phingofficial/phing/pull/1109)
 * IntrospectionHelper should not convert to bool, if a typehinted string was found. [\#1108](https://github.com/phingofficial/phing/pull/1108)
 * Updated scrutinizer config [\#1107](https://github.com/phingofficial/phing/pull/1107)
 * Only send coverage report if not phpcs build [\#1106](https://github.com/phingofficial/phing/pull/1106)
 * Added iterator support for FileList [\#1105](https://github.com/phingofficial/phing/pull/1105)
 * Added reference check for FileSet::getIterator() [\#1104](https://github.com/phingofficial/phing/pull/1104)
 * foreach with filelist causes fatal error [\#1103](https://github.com/phingofficial/phing/issues/1103)
 * PSR-12 [\#1097](https://github.com/phingofficial/phing/pull/1097)
 * [phpcs] phpcs 3 compatible task [\#1096](https://github.com/phingofficial/phing/pull/1096)
 * Update obsolete phpstan --errorFormat flag with correct one [\#1095](https://github.com/phingofficial/phing/pull/1095)
 * istrue treats undefined property as "true" [\#1093](https://github.com/phingofficial/phing/issues/1093)
 * PHPStanTask with Fileset support [\#1091](https://github.com/phingofficial/phing/pull/1091)
 * Reduced code [\#1090](https://github.com/phingofficial/phing/pull/1090)
 * [HttpCondition] Removed deprecated constant php 7.3 compat [\#1089](https://github.com/phingofficial/phing/pull/1089)
 * Update docker instructions [\#1087](https://github.com/phingofficial/phing/pull/1087)
 * Feature/visualizer theming [\#1084](https://github.com/phingofficial/phing/pull/1084)
 * Error while using AutoloaderTask [\#1080](https://github.com/phingofficial/phing/issues/1080)
 * [StopWatch] Fixed visibility of action methods. [\#1079](https://github.com/phingofficial/phing/pull/1079)
 * Used getDataTypeName instead of legacy code [\#1073](https://github.com/phingofficial/phing/pull/1073)
 * Added missing license headers. [\#1072](https://github.com/phingofficial/phing/pull/1072)
 * Removed deprecated scpsend alias [\#1071](https://github.com/phingofficial/phing/pull/1071)
 * Removed not used test file. [\#1070](https://github.com/phingofficial/phing/pull/1070)
 * [ApplyTask] Fixed wrong condition [\#1069](https://github.com/phingofficial/phing/pull/1069)
 * PHPUnit removed hack [\#1061](https://github.com/phingofficial/phing/pull/1061)
 * Removed PHPUnit\Util\Log\JUnit::setWriteDocument() [\#1060](https://github.com/phingofficial/phing/pull/1060)
 * Made some more args optional [\#1059](https://github.com/phingofficial/phing/pull/1059)
 * Made some args optional. [\#1058](https://github.com/phingofficial/phing/pull/1058)
 * Removed unused method [\#1056](https://github.com/phingofficial/phing/pull/1056)
 * fieldsets not supported by phpstan task [\#1055](https://github.com/phingofficial/phing/issues/1055)
 * Dependencies missing in PHAR [\#1053](https://github.com/phingofficial/phing/issues/1053)
 * Reduced code [\#1050](https://github.com/phingofficial/phing/pull/1050)
 * Removed duplicate and unused method [\#1049](https://github.com/phingofficial/phing/pull/1049)
 * Added StatisticsListener to the travis builds [\#1048](https://github.com/phingofficial/phing/pull/1048)
 * Reduced duplicate code [\#1047](https://github.com/phingofficial/phing/pull/1047)
 * Added missing precondition checks for references [\#1045](https://github.com/phingofficial/phing/pull/1045)
 * Fixed DirectoryScanner - wrong method call [\#1044](https://github.com/phingofficial/phing/pull/1044)
 * Fixed line ending issue in SuffixLines [\#1043](https://github.com/phingofficial/phing/pull/1043)
 * Fixed relaxng validation errors [\#1042](https://github.com/phingofficial/phing/pull/1042)
 * SuffixLines filter does not preserve line endings [\#1041](https://github.com/phingofficial/phing/issues/1041)
 * Added event debug logs. [\#1040](https://github.com/phingofficial/phing/pull/1040)
 * Added support for object::__toString inside EventObject::__toString [\#1039](https://github.com/phingofficial/phing/pull/1039)
 * Added version upperbound to pear/http_request2 [\#1038](https://github.com/phingofficial/phing/pull/1038)
 * LineContains uses readLine [\#1033](https://github.com/phingofficial/phing/pull/1033)
 * Replaced while...each loops with foreach. [\#1032](https://github.com/phingofficial/phing/pull/1032)
 * Bug when using <linecontains> filter on large files [\#1030](https://github.com/phingofficial/phing/issues/1030)
 * Improved debug log - ref to string if possible [\#1029](https://github.com/phingofficial/phing/pull/1029)
 * Added test cases. [\#1028](https://github.com/phingofficial/phing/pull/1028)
 * Fixed single test execution of phpunit tests. [\#1027](https://github.com/phingofficial/phing/pull/1027)
 * Fixed phing test execution under PHPUnit 8 [\#1024](https://github.com/phingofficial/phing/pull/1024)
 * PatchTask extensions [\#1023](https://github.com/phingofficial/phing/pull/1023)
 * Feature/visualizer task [\#1019](https://github.com/phingofficial/phing/pull/1019)

Jan. 4, 2019 - Phing 3.0.0 alpha 2
----------------------------------

The following issues and pull requests were closed in this release:

 * No need for verbose log on ref change for UE [\#1007](https://github.com/phingofficial/phing/pull/1007)
 * Removed include_once [\#1006](https://github.com/phingofficial/phing/pull/1006)
 * Opened IoncubeEncoderTask to all php versions [\#1005](https://github.com/phingofficial/phing/pull/1005)
 * Added DisableInputHandler [\#1004](https://github.com/phingofficial/phing/pull/1004)
 * Feature request: Disable Input [\#1003](https://github.com/phingofficial/phing/issues/1003)
 * Third $parentDir param should be optional [\#1002](https://github.com/phingofficial/phing/pull/1002)
 * Extended tstamp task. [\#995](https://github.com/phingofficial/phing/pull/995)
 * Added test for basename task. [\#994](https://github.com/phingofficial/phing/pull/994)
 * Removed obsolete windows test file [\#993](https://github.com/phingofficial/phing/pull/993)
 * Skipped git task tests on windows [\#992](https://github.com/phingofficial/phing/pull/992)
 * StopwatchTask should use DispatchTask [\#991](https://github.com/phingofficial/phing/pull/991)
 * Use dedicated PHPUnit assertions for better error messages [\#990](https://github.com/phingofficial/phing/pull/990)
 * Refactor foreach loop into in_array call [\#989](https://github.com/phingofficial/phing/pull/989)
 * Appveyor should not update composer deps [\#987](https://github.com/phingofficial/phing/pull/987)
 * Added exception message to verbose output. [\#986](https://github.com/phingofficial/phing/pull/986)
 * Fix PropertyCopy documentation [\#985](https://github.com/phingofficial/phing/pull/985)
 * Fix coding standards issues and docblocks. [\#982](https://github.com/phingofficial/phing/pull/982)
 * Make PHPStan task generate error messages and allow skipping see #980 [\#981](https://github.com/phingofficial/phing/pull/981)
 * PHPStan Task does not fail during errors [\#980](https://github.com/phingofficial/phing/issues/980)
 * The introduction seemed a bit dated, so updated it a bit.  Also added some minor punctuation fixes. [\#979](https://github.com/phingofficial/phing/pull/979)
 * Added Svn Revert Task [\#977](https://github.com/phingofficial/phing/pull/977)
 * Fixed file comments for better API generation [\#976](https://github.com/phingofficial/phing/pull/976)
 * Removed outdated todo [\#975](https://github.com/phingofficial/phing/pull/975)
 * Updated appveyor config [\#974](https://github.com/phingofficial/phing/pull/974)
 * Added URLEncodeTask. [\#973](https://github.com/phingofficial/phing/pull/973)
 * Avoid calling get_class on null in UnknownElement. [\#972](https://github.com/phingofficial/phing/pull/972)
 * Added preserve duplicates to PathConvert [\#969](https://github.com/phingofficial/phing/pull/969)
 * Fixed null pointer exception [\#968](https://github.com/phingofficial/phing/pull/968)
 * Add verbosity to VersionTask [\#967](https://github.com/phingofficial/phing/pull/967)
 * Added index to foreach task [\#963](https://github.com/phingofficial/phing/pull/963)
 * Added PHPUnit 7 support. [\#962](https://github.com/phingofficial/phing/pull/962)
 * Added silent flag to symfony console task [\#961](https://github.com/phingofficial/phing/pull/961)
 * SymfonyConsole - ProgressBar output incorrect [\#960](https://github.com/phingofficial/phing/issues/960)
 * VersionTask can manage 'v' prefix [\#955](https://github.com/phingofficial/phing/pull/955)
 * Compatability with phpunit7? [\#952](https://github.com/phingofficial/phing/issues/952)
 * #946: Trim outputProperty value of GitLogTask. [\#947](https://github.com/phingofficial/phing/pull/947)
 * Fix for archive task [\#945](https://github.com/phingofficial/phing/pull/945)
 * Missing ${file.separator} ? [\#943](https://github.com/phingofficial/phing/issues/943)
 * Cleanup of #735 - part 4 [\#940](https://github.com/phingofficial/phing/pull/940)
 * Cleanup of #735 - part 3 [\#939](https://github.com/phingofficial/phing/pull/939)
 * Cleanup of #735 - part 2 [\#938](https://github.com/phingofficial/phing/pull/938)
 * Fixed DefaultExcludes by removing an old hhvm fix [\#937](https://github.com/phingofficial/phing/pull/937)
 * Expanding a Property Reference with ${toString:} [\#936](https://github.com/phingofficial/phing/pull/936)
 * Fixed test execution. [\#935](https://github.com/phingofficial/phing/pull/935)
 * Added AnsiColorLogger and SilentLogger to the docs [\#932](https://github.com/phingofficial/phing/pull/932)
 * Added missing requirements for #826 [\#931](https://github.com/phingofficial/phing/pull/931)
 * Added file attribute of fileset to doc and grammar [\#927](https://github.com/phingofficial/phing/pull/927)
 * Added nested params to PhpEvalTask [\#926](https://github.com/phingofficial/phing/pull/926)
 * Mapper support for PathConvert task. [\#925](https://github.com/phingofficial/phing/pull/925)
 * Added blockfor task to grammar [\#924](https://github.com/phingofficial/phing/pull/924)
 * Added project instance and location to targets [\#923](https://github.com/phingofficial/phing/pull/923)
 * Small additions to the os condition [\#922](https://github.com/phingofficial/phing/pull/922)
 * Fixed verbose logging of exception traces. [\#921](https://github.com/phingofficial/phing/pull/921)
 * 'notify-send' is not recognized as an internal or external command [\#915](https://github.com/phingofficial/phing/issues/915)
 * Consistent usage of to string [\#913](https://github.com/phingofficial/phing/pull/913)
 * Made ProjectConfigurator::__construct private [\#912](https://github.com/phingofficial/phing/pull/912)
 * Added missing strict attrib to grammar [\#911](https://github.com/phingofficial/phing/pull/911)
 * Added missing logskipped attrib for targets in grammar [\#910](https://github.com/phingofficial/phing/pull/910)
 * PHPStan task [\#908](https://github.com/phingofficial/phing/pull/908)
 * Added html attribute to XsltTask [\#907](https://github.com/phingofficial/phing/pull/907)
 * Be less restrictive on TaskContainer::addTask(Task) [\#906](https://github.com/phingofficial/phing/pull/906)
 * Take care of PHP return types in IntrospectionHelper [\#905](https://github.com/phingofficial/phing/pull/905)
 * Fixes #560 [\#904](https://github.com/phingofficial/phing/pull/904)
 * Made XML based property files loadable. [\#903](https://github.com/phingofficial/phing/pull/903)
 * Fixed #887 [\#902](https://github.com/phingofficial/phing/pull/902)
 * Removed obsolete php version check [\#901](https://github.com/phingofficial/phing/pull/901)
 * Fixed condition of child nodes at prefix building in XmlPropertyTask [\#900](https://github.com/phingofficial/phing/pull/900)
 * Fixed nested condition test in FailTask [\#899](https://github.com/phingofficial/phing/pull/899)
 * Fixed PhingFile::createNewFile if parent is null [\#898](https://github.com/phingofficial/phing/pull/898)
 * Fixed isBoolean check. [\#897](https://github.com/phingofficial/phing/pull/897)
 * Update Phing.php [\#896](https://github.com/phingofficial/phing/pull/896)
 * Fixed undefined constant. [\#895](https://github.com/phingofficial/phing/pull/895)
 * Refactors SassTask [\#892](https://github.com/phingofficial/phing/pull/892)
 * Generated .phar is a bit big [\#891](https://github.com/phingofficial/phing/issues/891)
 * Fix PHP CodeSniffer cache write to directory [\#890](https://github.com/phingofficial/phing/pull/890)
 * Adds some SassTask tests [\#889](https://github.com/phingofficial/phing/pull/889)
 * Failing test ForeachTaskTest::testLogMessageWithFileset [\#887](https://github.com/phingofficial/phing/issues/887)
 * Removed `fallback` part of the `PropertyTask` documentation [\#885](https://github.com/phingofficial/phing/pull/885)
 * Cleanup of #735 - part 1 [\#878](https://github.com/phingofficial/phing/pull/878)
 * Enable HttpRequestTask to validate response codes [\#824](https://github.com/phingofficial/phing/issues/824)
 * SVN Revert task [\#805](https://github.com/phingofficial/phing/issues/805)
 * [WIP] Small improvement on comparing files. [\#785](https://github.com/phingofficial/phing/pull/785)
 * [WIP] Fixed whitespace issue on argument escaping. [\#735](https://github.com/phingofficial/phing/pull/735)
 * Unwanted spaces in attribute with forced escape in ExecTask [\#637](https://github.com/phingofficial/phing/issues/637)
 * MkdirTask behaves the same as "mkdir" Linux command and respects POSIX ACL [\#591](https://github.com/phingofficial/phing/pull/591)
 * Include (most used) dependencies in phar (Trac #1113) [\#566](https://github.com/phingofficial/phing/issues/566)

Mar. 23, 2018 - Phing 3.0.0 alpha 1
-----------------------------------

The following issues were closed in this release:

 * fixed typos in error messages [\#888](https://github.com/phingofficial/phing/issues/888)
 * Refactor SassTask tests [\#882](https://github.com/phingofficial/phing/issues/882)
 * The is_executable check in the Which method when run on Windows is unnecessary. [\#880](https://github.com/phingofficial/phing/issues/880)
 * Fixed #712 [\#879](https://github.com/phingofficial/phing/issues/879)
 * Added missing method DataType::getDataTypeName() [\#864](https://github.com/phingofficial/phing/issues/864)
 * Removed unused methods in StringHelper [\#863](https://github.com/phingofficial/phing/issues/863)
 * Fixed ConsoleInputHandler for symfony 4 [\#862](https://github.com/phingofficial/phing/issues/862)
 * Fixed regression test 309 for win [\#860](https://github.com/phingofficial/phing/issues/860)
 * Fixed FileUtils::contentEquals [\#859](https://github.com/phingofficial/phing/issues/859)
 * ConsoleInputHandler isn't Symfony 4 compatible [\#858](https://github.com/phingofficial/phing/issues/858)
 * Added multiple property file inclusion. [\#856](https://github.com/phingofficial/phing/issues/856)
 * Fixed wrong init value [\#855](https://github.com/phingofficial/phing/issues/855)
 * Fixed FatalError in ZendGuardFileSet [\#854](https://github.com/phingofficial/phing/issues/854)
 * Optimized api build file [\#852](https://github.com/phingofficial/phing/issues/852)
 * Fixed grammar for phpdoc2 task. [\#851](https://github.com/phingofficial/phing/issues/851)
 * Removed more include statements [\#850](https://github.com/phingofficial/phing/issues/850)
 * Removed includes/requires from test sources. [\#849](https://github.com/phingofficial/phing/issues/849)
 * Removed unused ident [\#846](https://github.com/phingofficial/phing/issues/846)
 * Added SvnProp* tasks [\#845](https://github.com/phingofficial/phing/issues/845)
 * Updated supported php version [\#844](https://github.com/phingofficial/phing/issues/844)
 * Added ClasspathAware trait. [\#843](https://github.com/phingofficial/phing/issues/843)
 * Get rid of FunctionParam class. [\#842](https://github.com/phingofficial/phing/issues/842)
 * Added selectors to the grammar file. [\#841](https://github.com/phingofficial/phing/issues/841)
 * Removed hhvm build from travis - added php nightly [\#839](https://github.com/phingofficial/phing/issues/839)
 * Removed unused methods. [\#838](https://github.com/phingofficial/phing/issues/838)
 * Fixed method call on duplicated targets. [\#837](https://github.com/phingofficial/phing/issues/837)
 * Removed includes for phing own classes [\#836](https://github.com/phingofficial/phing/issues/836)
 * Removed IterableFileSet [\#835](https://github.com/phingofficial/phing/issues/835)
 * Removed settings of deprecated ini options [\#834](https://github.com/phingofficial/phing/issues/834)
 * Simplified Character::isLetter() [\#833](https://github.com/phingofficial/phing/issues/833)
 * Made DateSelector::setMillis() public [\#832](https://github.com/phingofficial/phing/issues/832)
 * Improved error/exception reporting in Task::perform() [\#831](https://github.com/phingofficial/phing/issues/831)
 * Added public setter/getter to reference object. [\#830](https://github.com/phingofficial/phing/issues/830)
 * Target attrib of PhingTask must not be empty. [\#827](https://github.com/phingofficial/phing/issues/827)
 * Included Listener/Logger chapter in master.xml [\#822](https://github.com/phingofficial/phing/issues/822)
 * Updated documentation - FileSyncTask [\#820](https://github.com/phingofficial/phing/issues/820)
 * Fixed call to a private member var. [\#819](https://github.com/phingofficial/phing/issues/819)
 * Fixed exclude/include groups for phpunit 6. [\#818](https://github.com/phingofficial/phing/issues/818)
 * Git branch [\#817](https://github.com/phingofficial/phing/issues/817)
 * GitBranchTask failes with git >= 2.15.0 [\#816](https://github.com/phingofficial/phing/issues/816)
 * Fixed composer install issue [\#815](https://github.com/phingofficial/phing/issues/815)
 * Bump minimum PHP version to 7.0+ [\#813](https://github.com/phingofficial/phing/issues/813)
 * PharPackageTask wrong format of path in webstub and/or clistub when building on Windows [\#809](https://github.com/phingofficial/phing/issues/809)
 * Cannot make work PHPUnit 6 [\#802](https://github.com/phingofficial/phing/issues/802)
 * Can't install dev-master version using Composer [\#799](https://github.com/phingofficial/phing/issues/799)
 * Fixed generation of html reportfiles. [\#798](https://github.com/phingofficial/phing/issues/798)
 * Init feature [\#796](https://github.com/phingofficial/phing/issues/796)
 * Added type aware traits. [\#783](https://github.com/phingofficial/phing/issues/783)
 * Added regex attrib to the filename selector. [\#782](https://github.com/phingofficial/phing/issues/782)
 * Added casesensitive and handledirsep attribs to the regexp mapper. [\#781](https://github.com/phingofficial/phing/issues/781)
 * Added casesensitive and handledirsep attribs to the glob mapper. [\#780](https://github.com/phingofficial/phing/issues/780)
 * Added multline attribute to containsregexp selector. [\#779](https://github.com/phingofficial/phing/issues/779)
 * Added negate, regexp, casesensitive attribs to linecontainsregexp filter [\#778](https://github.com/phingofficial/phing/issues/778)
 * Added negate attribute to the `linecontains` filter. [\#777](https://github.com/phingofficial/phing/issues/777)
 * Fixed log method - HttpGetTask. [\#771](https://github.com/phingofficial/phing/issues/771)
 * Added stopwatch name to log output. [\#767](https://github.com/phingofficial/phing/issues/767)
 * feature request: stopwatch should show name as well  [\#765](https://github.com/phingofficial/phing/issues/765)
 * stopwatch includes autoloader [\#764](https://github.com/phingofficial/phing/issues/764)
 * Fix: php.ini variable evaluation and "Notice: A non well formed numeric value encountered" [\#761](https://github.com/phingofficial/phing/issues/761)
 * Added ability logging exceptions. [\#760](https://github.com/phingofficial/phing/issues/760)
 * Added location setting to all task defined. [\#759](https://github.com/phingofficial/phing/issues/759)
 * Added DependSet task. [\#757](https://github.com/phingofficial/phing/issues/757)
 * Added FileList support to the TouchTask [\#756](https://github.com/phingofficial/phing/issues/756)
 * Added osfamily attribute to ExecTask [\#755](https://github.com/phingofficial/phing/issues/755)
 * Fixed usage of filelist if PathConvert uses a reference. [\#754](https://github.com/phingofficial/phing/issues/754)
 * Superseded #302 Remove S3 PEAR dependency [\#748](https://github.com/phingofficial/phing/issues/748)
 * Added relentless task. [\#746](https://github.com/phingofficial/phing/issues/746)
 * StatisticsListener [\#744](https://github.com/phingofficial/phing/issues/744)
 * Default exclude task [\#740](https://github.com/phingofficial/phing/issues/740)
 * Fixed PropertyConditions behavior. [\#739](https://github.com/phingofficial/phing/issues/739)
 * Fixed deprecated function calls. [\#737](https://github.com/phingofficial/phing/issues/737)
 * SCA with Php Inspections (EA Extended) [\#731](https://github.com/phingofficial/phing/issues/731)
 * Added PHPLoc ^4 support. [\#729](https://github.com/phingofficial/phing/issues/729)
 * PHPLoc Task: Wrong class name in CSV Formatter [\#725](https://github.com/phingofficial/phing/issues/725)
 * PHPLoc task Wrong class name for XML Logger class [\#724](https://github.com/phingofficial/phing/issues/724)
 * HttpRequestTask doesn't support POST application/json [\#715](https://github.com/phingofficial/phing/issues/715)
 * SassTask: Consider removing/embeding the dependency on Pear::System [\#710](https://github.com/phingofficial/phing/issues/710)
 * Parallel Task: Call to a member function push() on null in ... Manager.php:237 [\#706](https://github.com/phingofficial/phing/issues/706)
 * Dynamic path for composer task [\#701](https://github.com/phingofficial/phing/issues/701)
 * patchTask not shell escaping file paths [\#693](https://github.com/phingofficial/phing/issues/693)
 * Relative Symlinks [\#684](https://github.com/phingofficial/phing/issues/684)
 * NullPointerException when phploc is used without a formatter [\#683](https://github.com/phingofficial/phing/issues/683)
 * Always interpret basedir as relative to project's root [\#668](https://github.com/phingofficial/phing/issues/668)
 * phpunit task is not compatible with PHPUnit 6.0 [\#659](https://github.com/phingofficial/phing/issues/659)
 * symfony/yaml dependency improvemnt [\#658](https://github.com/phingofficial/phing/issues/658)
 * Deprecate the PEAR channel [\#657](https://github.com/phingofficial/phing/issues/657)
 * Making phing compatible with phive (https://phar.io) [\#633](https://github.com/phingofficial/phing/issues/633)
 * Phing Strict Build Mode [\#626](https://github.com/phingofficial/phing/issues/626)
 * Adding 0 and 1 strings as true and false values in StringHelper. [\#590](https://github.com/phingofficial/phing/issues/590)
 * PHPUnitReportTask fails with XSLTProcessor::importStylesheet() unable to read phar:/usr/local/bin/phing/etc/str.replace.function.xsl (Trac #1240) [\#584](https://github.com/phingofficial/phing/issues/584)
 * Unit test for various Git and SVN related tasks fails if locale is not 'en' or 'C' (Trac #1213) [\#577](https://github.com/phingofficial/phing/issues/577)
 * Phingcall should have the options returnProperty (Trac #1209) [\#576](https://github.com/phingofficial/phing/issues/576)
 * add task for git archive or git checkout-index (Trac #1182) [\#573](https://github.com/phingofficial/phing/issues/573)
 * Error overwriting symlinks on copy or move (Trac #1096) [\#562](https://github.com/phingofficial/phing/issues/562)
 * Support <dirset> in chmod, chown, delete, echo, copy, foreach and move tasks (Trac #1026) [\#559](https://github.com/phingofficial/phing/issues/559)
 * ComposerTask when composer is installed in the system (Trac #1008) [\#558](https://github.com/phingofficial/phing/issues/558)
 * phing should get a strict mode (Trac #918) [\#554](https://github.com/phingofficial/phing/issues/554)
 * Add 'hide input' attribute to InputTask (Trac #885) [\#553](https://github.com/phingofficial/phing/issues/553)
 * Find build.xml file in parent directory tree (Trac #864) [\#551](https://github.com/phingofficial/phing/issues/551)
 * includePath using project.basedir is failing under certain conditions (Trac #586) [\#537](https://github.com/phingofficial/phing/issues/537)
 * Properties not being set on subsequent sets. (Trac #511) [\#535](https://github.com/phingofficial/phing/issues/535)
 * Build Progress Bar (Trac #305) [\#532](https://github.com/phingofficial/phing/issues/532)
 * Document that in a FileSet include/exclude "foo/" means "foo/**" [\#367](https://github.com/phingofficial/phing/issues/367)
 * Make basedir property (including its default value) a path relative to the buildfile [\#358](https://github.com/phingofficial/phing/issues/358)
 * Remove S3 PEAR dependency [\#302](https://github.com/phingofficial/phing/issues/302)
 * Consider the strings "1" and "0" to be true and false, respectively. [\#261](https://github.com/phingofficial/phing/issues/261)
 * Phing Strict Build Mode [\#159](https://github.com/phingofficial/phing/issues/159)

The following pull requests were merged in this release:

 * fixed typos in error messages [\#888](https://github.com/phingofficial/phing/pulls/888)
 * Refactor SassTask tests [\#882](https://github.com/phingofficial/phing/pulls/882)
 * The is_executable check in the Which method when run on Windows is unnecessary. [\#880](https://github.com/phingofficial/phing/pulls/880)
 * Fixed #712 [\#879](https://github.com/phingofficial/phing/pulls/879)
 * Added missing method DataType::getDataTypeName() [\#864](https://github.com/phingofficial/phing/pulls/864)
 * Removed unused methods in StringHelper [\#863](https://github.com/phingofficial/phing/pulls/863)
 * Fixed ConsoleInputHandler for symfony 4 [\#862](https://github.com/phingofficial/phing/pulls/862)
 * Fixed regression test 309 for win [\#860](https://github.com/phingofficial/phing/pulls/860)
 * Fixed FileUtils::contentEquals [\#859](https://github.com/phingofficial/phing/pulls/859)
 * Added multiple property file inclusion. [\#856](https://github.com/phingofficial/phing/pulls/856)
 * Fixed wrong init value [\#855](https://github.com/phingofficial/phing/pulls/855)
 * Fixed FatalError in ZendGuardFileSet [\#854](https://github.com/phingofficial/phing/pulls/854)
 * Optimized api build file [\#852](https://github.com/phingofficial/phing/pulls/852)
 * Fixed grammar for phpdoc2 task. [\#851](https://github.com/phingofficial/phing/pulls/851)
 * Removed more include statements [\#850](https://github.com/phingofficial/phing/pulls/850)
 * Removed includes/requires from test sources. [\#849](https://github.com/phingofficial/phing/pulls/849)
 * Removed unused ident [\#846](https://github.com/phingofficial/phing/pulls/846)
 * Added SvnProp* tasks [\#845](https://github.com/phingofficial/phing/pulls/845)
 * Updated supported php version [\#844](https://github.com/phingofficial/phing/pulls/844)
 * Added ClasspathAware trait. [\#843](https://github.com/phingofficial/phing/pulls/843)
 * Get rid of FunctionParam class. [\#842](https://github.com/phingofficial/phing/pulls/842)
 * Added selectors to the grammar file. [\#841](https://github.com/phingofficial/phing/pulls/841)
 * Removed hhvm build from travis - added php nightly [\#839](https://github.com/phingofficial/phing/pulls/839)
 * Removed unused methods. [\#838](https://github.com/phingofficial/phing/pulls/838)
 * Fixed method call on duplicated targets. [\#837](https://github.com/phingofficial/phing/pulls/837)
 * Removed includes for phing own classes [\#836](https://github.com/phingofficial/phing/pulls/836)
 * Removed IterableFileSet [\#835](https://github.com/phingofficial/phing/pulls/835)
 * Removed settings of deprecated ini options [\#834](https://github.com/phingofficial/phing/pulls/834)
 * Simplified Character::isLetter() [\#833](https://github.com/phingofficial/phing/pulls/833)
 * Made DateSelector::setMillis() public [\#832](https://github.com/phingofficial/phing/pulls/832)
 * Improved error/exception reporting in Task::perform() [\#831](https://github.com/phingofficial/phing/pulls/831)
 * Added public setter/getter to reference object. [\#830](https://github.com/phingofficial/phing/pulls/830)
 * Target attrib of PhingTask must not be empty. [\#827](https://github.com/phingofficial/phing/pulls/827)
 * Included Listener/Logger chapter in master.xml [\#822](https://github.com/phingofficial/phing/pulls/822)
 * Updated documentation - FileSyncTask [\#820](https://github.com/phingofficial/phing/pulls/820)
 * Fixed call to a private member var. [\#819](https://github.com/phingofficial/phing/pulls/819)
 * Fixed exclude/include groups for phpunit 6. [\#818](https://github.com/phingofficial/phing/pulls/818)
 * Git branch [\#817](https://github.com/phingofficial/phing/pulls/817)
 * Fixed composer install issue [\#815](https://github.com/phingofficial/phing/pulls/815)
 * Fixed generation of html reportfiles. [\#798](https://github.com/phingofficial/phing/pulls/798)
 * Added type aware traits. [\#783](https://github.com/phingofficial/phing/pulls/783)
 * Added regex attrib to the filename selector. [\#782](https://github.com/phingofficial/phing/pulls/782)
 * Added casesensitive and handledirsep attribs to the regexp mapper. [\#781](https://github.com/phingofficial/phing/pulls/781)
 * Added casesensitive and handledirsep attribs to the glob mapper. [\#780](https://github.com/phingofficial/phing/pulls/780)
 * Added multline attribute to containsregexp selector. [\#779](https://github.com/phingofficial/phing/pulls/779)
 * Added negate, regexp, casesensitive attribs to linecontainsregexp filter [\#778](https://github.com/phingofficial/phing/pulls/778)
 * Added negate attribute to the `linecontains` filter. [\#777](https://github.com/phingofficial/phing/pulls/777)
 * Fixed log method - HttpGetTask. [\#771](https://github.com/phingofficial/phing/pulls/771)
 * Added stopwatch name to log output. [\#767](https://github.com/phingofficial/phing/pulls/767)
 * Fix: php.ini variable evaluation and "Notice: A non well formed numeric value encountered" [\#761](https://github.com/phingofficial/phing/pulls/761)
 * Added ability logging exceptions. [\#760](https://github.com/phingofficial/phing/pulls/760)
 * Added DependSet task. [\#757](https://github.com/phingofficial/phing/pulls/757)
 * Added FileList support to the TouchTask [\#756](https://github.com/phingofficial/phing/pulls/756)
 * Added osfamily attribute to ExecTask [\#755](https://github.com/phingofficial/phing/pulls/755)
 * Fixed usage of filelist if PathConvert uses a reference. [\#754](https://github.com/phingofficial/phing/pulls/754)
 * Superseded #302 Remove S3 PEAR dependency [\#748](https://github.com/phingofficial/phing/pulls/748)
 * Added relentless task. [\#746](https://github.com/phingofficial/phing/pulls/746)
 * StatisticsListener [\#744](https://github.com/phingofficial/phing/pulls/744)
 * Default exclude task [\#740](https://github.com/phingofficial/phing/pulls/740)
 * Fixed PropertyConditions behavior. [\#739](https://github.com/phingofficial/phing/pulls/739)
 * Fixed deprecated function calls. [\#737](https://github.com/phingofficial/phing/pulls/737)
 * SCA with Php Inspections (EA Extended) [\#731](https://github.com/phingofficial/phing/pulls/731)
 * Added PHPLoc ^4 support. [\#729](https://github.com/phingofficial/phing/pulls/729)
 * Dynamic path for composer task [\#701](https://github.com/phingofficial/phing/pulls/701)
 * Phing Strict Build Mode [\#626](https://github.com/phingofficial/phing/pulls/626)
 * Adding 0 and 1 strings as true and false values in StringHelper. [\#590](https://github.com/phingofficial/phing/pulls/590)
 * Document that in a FileSet include/exclude "foo/" means "foo/**" [\#367](https://github.com/phingofficial/phing/pulls/367)
 * Make basedir property (including its default value) a path relative to the buildfile [\#358](https://github.com/phingofficial/phing/pulls/358)

Jan. 25, 2018 - Phing 2.16.1
----------------------------

This patch release fixes the following issue:

 * Allow Symfony 4 [\#807](https://github.com/phingofficial/phing/pull/807)

Dec. 22, 2016 - Phing 2.16.0
----------------------------

This release contains the following new or improved functionality:

 * Append, Property, Sleep, Sonar and Truncate tasks
 * Improved PHP 7.1 compatibility
 * Various typo and bug fixes, documentation updates

This release will most likely be the last minor update in the 2.x series. Phing 3.x will drop support for PHP < 5.6.

The following issues were closed in this release:

 * phing should get a strict mode \(Trac \#918\) [\#554](https://github.com/phingofficial/phing/issues/554)
 * Can not delete git folders on windows \(Trac \#956\) [\#556](https://github.com/phingofficial/phing/issues/556)
 * Relative symlinks \(Trac \#1124\) [\#567](https://github.com/phingofficial/phing/issues/567)
 * Tests fail under windows \(Trac \#1215\)  [\#578](https://github.com/phingofficial/phing/issues/578)
 * stripphpcomments matches links in html \(Trac \#1219\) [\#579](https://github.com/phingofficial/phing/issues/579)
 * OS detection fails on OSX \(Trac \#1227\) [\#581](https://github.com/phingofficial/phing/issues/581)
 * JsHintTask fails when reporter attribute is not set \(Trac \#1230\) [\#582](https://github.com/phingofficial/phing/issues/582)
 * An issue with 'file' attribute of 'append' task \(v2.15.1\) [\#595](https://github.com/phingofficial/phing/issues/595)
 * An issue with 'append' task when adding a list of files in a directory \(v2.15.1\) [\#597](https://github.com/phingofficial/phing/issues/597)
 * Git auto modified file with phing vendor [\#613](https://github.com/phingofficial/phing/issues/613)
 * phar file not working - \Symfony\Component\Yaml\Parser' not found [\#614](https://github.com/phingofficial/phing/issues/614)
 * JSHint - Support of specific config file path [\#615](https://github.com/phingofficial/phing/issues/615)
 * PHP notice on 7.1: A non well formed numeric value encountered [\#622](https://github.com/phingofficial/phing/issues/622)
 * Sass task fails when PEAR is not installed [\#624](https://github.com/phingofficial/phing/issues/624)
 * sha-512 hash for phing-latest.phar [\#629](https://github.com/phingofficial/phing/issues/629)

Oct. 13, 2016 - Phing 2.15.2
----------------------------

This release fixes a regression introduced in 2.15.1:

 * #593 - Changed behavior in <fileset/> filtering in 2.15.1

Oct. 11, 2016 - Phing 2.15.1
----------------------------

This release fixes a missing include and two bugs:

 * [https://www.phing.info/trac/ticket/1264] delete fileset /foo.php deletes /baz.foo.php
 * [https://www.phing.info/trac/ticket/1038] PhingFile getPathWithoutBase does not work for files outside basedir

Sep. 14, 2016 - Phing 2.15.0
----------------------------

This release contains the following new or improved functionality:

 * PHP 7.0 compatibility was improved
 * Phing grammar was updated
 * Tasks to work with Mercurial were added
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1263] Error in SassTask on PHP 7
 * [https://www.phing.info/trac/ticket/1262] Fatal error in SassTask when Sass gem is not installed
 * [https://www.phing.info/trac/ticket/1259] PHP_CLASSPATH Enviroment Variable
 * [https://www.phing.info/trac/ticket/1258] ApigenTask issue
 * [https://www.phing.info/trac/ticket/1257] The phpunit-code-coverage version 4.x breaks the phing-tasks-phpunit component
 * [https://www.phing.info/trac/ticket/1254] ftpdeploy : [PHP Error] require_once(PEAR.php): failed to open stream: No such file or directory [line 251 of site\vendor\phing\phing\src\Task\Ext\FtpDeploy.php]
 * [https://www.phing.info/trac/ticket/1253] Phing gitlog task not return last commit when committer's system time is set forward
 * [https://www.phing.info/trac/ticket/1249] First tstamp task is generating wrong timestamp
 * [https://www.phing.info/trac/ticket/1247] IsProperty(True/False)Condition doesn't support the 'name' attribute
 * [https://www.phing.info/trac/ticket/1246] FailTask with nested condition always fails
 * [https://www.phing.info/trac/ticket/1243] Command line argument with "|" character must be quoted
 * [https://www.phing.info/trac/ticket/1238] Add documentation for Smarty and ReplaceRegexp tasks
 * [https://www.phing.info/trac/ticket/566] Add Mercurial support

Mar. 10, 2016 - Phing 2.14.0
----------------------------

This release contains the following new or improved functionality:

 * Phing can now emit a specific status code on exit after failing
 * Added IsPropertyTrue/IsPropertyFalse conditions
 * Added IsWritable / IsReadable selectors
 * Added GitDescribe task
 * Added CutDirs mapper
 * Line breaks in property files on Windows machines fixed
 * FileSync task now supports excluding multiple files/directories
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1245] ExecTask documentation has incorrect escape attribute default value
 * [https://www.phing.info/trac/ticket/1244] phpunit task -- problem when listener depends on bootstrap
 * [https://www.phing.info/trac/ticket/1242] symfonyConsoleTask does not quote path to console
 * [https://www.phing.info/trac/ticket/1241] SymfonyConsoleTask's checkreturn / propertyname are not documented
 * [https://www.phing.info/trac/ticket/1239] ResolvePath just concatenates if "dir" attribute is present
 * [https://www.phing.info/trac/ticket/1237] HttpGetTask should catch HTTP_Request2_Exception, throw BuildException
 * [https://www.phing.info/trac/ticket/1236] version-compare condition typo in documentation
 * [https://www.phing.info/trac/ticket/1235] misworded sentence in documentation
 * [https://www.phing.info/trac/ticket/1234] IsFailure condition always evaluates to TRUE
 * [https://www.phing.info/trac/ticket/1231] JsHintTask fails when filename contains double quotes
 * [https://www.phing.info/trac/ticket/1198] PropertyTask resolving UTF-8 special chars in file attribute
 * [https://www.phing.info/trac/ticket/1194] Update relax-ng schema
 * [https://www.phing.info/trac/ticket/1132] Provide SHA512 sum of all generated archives for a release
 * [https://www.phing.info/trac/ticket/1131] Verification of changelog file fails when your file is in a directory added in your classpathref
 * [https://www.phing.info/trac/ticket/1046] ReplaceTokensWithFile doesn't support begintoken/endtokens with / in them

Dec. 4, 2015 - Phing 2.13.0
---------------------------

This release contains the following new or improved functionality:

 * '-listener' command line argument
 * SSL connections in FtpDeploy task
 * IsFailure condition
 * Crap4J PHPUnit formatter
 * FirstMatch mapper
 * PhpArrayMapLines filter
 * NotifySend, Attrib tasks
 * Json and Xml command line loggers
 * Property parser now supports YAML files
 * PHPUnit 5.x supported
 * PHP 7 fixes
 * Updated Apigen support
 * PhpCodeSniffer task can now populate a property with used sniffs
 * PHPMD and PhpCodeSniffer task can now cache results to speed up
   subsequent runs
 * Various typo and bug fixes, documentation updates

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1224] JSHint and space in the path of the workspace (Windows 7)
 * [https://www.phing.info/trac/ticket/1221] Case insensitive switch doesn't work
 * [https://www.phing.info/trac/ticket/1217] Add ability to ignore symlinks in zip task
 * [https://www.phing.info/trac/ticket/1212] Add support for formatters for PhpLoc task
 * [https://www.phing.info/trac/ticket/1187] Disable compression of phing.phar to make it work on hhvm

Aug. 24, 2015 - Phing 2.12.0
----------------------------

This release contains the following new or improved functionality:

 * Retry, Tempfile, Inifile tasks
 * 'keepgoing' command line mode
 * Fileset support in the Import task
 * EscapeUnicode, Concat filters
 * Profile logger
 * Composite mapper
 * Various typo and bug fixes

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1208] When UntarTask fails to extract an archive it should tell why
 * [https://www.phing.info/trac/ticket/1207] PackageAsPath Task exists in 2.11, but not in documentation
 * [https://www.phing.info/trac/ticket/1206] WaitFor task has maxwaitunit attribute, not WaitUnit
 * [https://www.phing.info/trac/ticket/1205] Triple "B.37.1 Supported Nested Tags" header
 * [https://www.phing.info/trac/ticket/1204] Wrong type of record task loglevel attribute
 * [https://www.phing.info/trac/ticket/1203] Duplicated doc for Apply task, spawn attribute
 * [https://www.phing.info/trac/ticket/1199] PHPUnitReport task: package name detection no longer works
 * [https://www.phing.info/trac/ticket/1196] Target 'phing.listener.AnsiColorLogger' does not exist in this project.
 * [https://www.phing.info/trac/ticket/1193] There is no native method for manipulating .ini files.
 * [https://www.phing.info/trac/ticket/1191] phing parallel task should handle workers dying unexpectedly
 * [https://www.phing.info/trac/ticket/1190] RegexTask processes backslashes incorrectly
 * [https://www.phing.info/trac/ticket/1189] Coverage Report broken for Jenkins PHP Clover
 * [https://www.phing.info/trac/ticket/1178] Parameter getValue is null when parameter is equal to 0
 * [https://www.phing.info/trac/ticket/1148] phpdoc2 via phar

May 20, 2015 - Phing 2.11.0
---------------------------

This release contains the following new or improved functionality:

 * PharData and EchoProperties tasks
 * 'silent' and 'emacs' command line modes
 * Improvements to FileHash and FtpDeploy tasks
 * SuffixLines and Sort filters

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1186] Implement pharLocation attribute for PHP_Depend task
 * [https://www.phing.info/trac/ticket/1185] Implement pharLocation attribute for PHPMD task
 * [https://www.phing.info/trac/ticket/1183] Fatal error in PHPMDTask
 * [https://www.phing.info/trac/ticket/1176] Showwarnings doesn't work
 * [https://www.phing.info/trac/ticket/1170] Allow more than one code standard review for PHP_CodeSniffer.
 * [https://www.phing.info/trac/ticket/1169] Allow for fuzzy parameter for phpcpdPHPCPD
 * [https://www.phing.info/trac/ticket/1162] add depth param to GitCloneTask
 * [https://www.phing.info/trac/ticket/1161] Update phpcpd & phploc tasks to work with phar versions
 * [https://www.phing.info/trac/ticket/1134] Phar version did not provide colorized output
 * [https://www.phing.info/trac/ticket/462] Incremental uploads in ftp deploy task

Feb. 19, 2015 - Phing 2.10.1
----------------------------

This release fixes the following tickets:

 * [https://www.phing.info/trac/ticket/1174] Phing can't work PHPUnit(PHAR)
 * [https://www.phing.info/trac/ticket/1173] [PHP Error] include_once(PHP/PPMD/Renderer/XMLRenderer.php): failed to open stream: No such file or directory
 * [https://www.phing.info/trac/ticket/1171] Socket condition does not work

Feb. 9, 2015 - Phing 2.10.0
---------------------------

This release contains the following new or improved functionality:

 * 'user.home' property on Windows fixed
 * Various documentation updates
 * Added support for listeners configured via phpunit.xml config
 * Basename task
 * Dirname task
 * Diagnostics task
 * FilesMatch condition
 * HasFreeSpace condition
 * PathToFileSet task
 * PhingVersion task/condition
 * PropertyRegex task
 * Recorder task
 * Socket condition
 * Xor condition

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1168] PhpCodeSnifferTask incompatible with PHP_CS 2.2.0
 * [https://www.phing.info/trac/ticket/1167] include task can't really have mode
 * [https://www.phing.info/trac/ticket/1163] Phing and PHPMD via composer both
 * [https://www.phing.info/trac/ticket/1160] Documentation lists covereage-report styledir as required.
 * [https://www.phing.info/trac/ticket/1159] phpunit task ignores excludeGroups, groups attributes
 * [https://www.phing.info/trac/ticket/1152] Add socket condition
 * [https://www.phing.info/trac/ticket/1127] Removing .phar from the phar file makes it crash
 * [https://www.phing.info/trac/ticket/1120] Phing 2.8.1 does not support PDepend 2.0
 * [https://www.phing.info/trac/ticket/856]  ZPK Packaging for zend server
 * [https://www.phing.info/trac/ticket/250]  recorder task

Dec. 3, 2014 - Phing 2.9.1
--------------------------

This releases fixes a Windows regression and adds the following new functionality:

 * Http condition
 * Switch task
 * Throw task

The following tickets were closed in this release:

 * [https://www.phing.info/trac/ticket/1158] Phing fails to call itself with Exec task
 * [https://www.phing.info/trac/ticket/1157] ZIP task ignores ${phing.dir}
 * [https://www.phing.info/trac/ticket/1156] phing+windows copy file path
 * [https://www.phing.info/trac/ticket/1155] Add http condition
 * [https://www.phing.info/trac/ticket/1154] Can't read version information file
 * [https://www.phing.info/trac/ticket/1147] Resetting Phing::$msgOutputLevel

Nov. 25, 2014 - Phing 2.9.0
---------------------------

This release contains the following new or improved functionality:

 * Phing now supports HHVM
 * Stopwatch task added
 * Unit test coverage increased
 * Source code formatted to PSR-2
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1151] PHPMD Task does not support the format tag
 * [https://www.phing.info/trac/ticket/1149] Exclude extra files from composer package
 * [https://www.phing.info/trac/ticket/1144] Reduce PhingCall/Foreach log messages
 * [https://www.phing.info/trac/ticket/1140] DefaultLogger is not default logger
 * [https://www.phing.info/trac/ticket/1138] ParallelTask - error in subtask should fail build
 * [https://www.phing.info/trac/ticket/1135] obfuscation-key option for IoncubeEncoderTask does not work
 * [https://www.phing.info/trac/ticket/1133] copytask haltonerror = "false" function failure when source dir not exists
 * [https://www.phing.info/trac/ticket/1130] Add documentation for Manifest task
 * [https://www.phing.info/trac/ticket/1129] ManifestTask md5 hash vs FileHashTask md5 hash not the same
 * [https://www.phing.info/trac/ticket/1128] Imported target won't run until there is one with the same name in main build.xml
 * [https://www.phing.info/trac/ticket/1123] ApplyTask outputProperty doesn't append
 * [https://www.phing.info/trac/ticket/1122] Untar task does not preserve file permissions
 * [https://www.phing.info/trac/ticket/1121] Please fix the syntax error in PHP Lint
 * [https://www.phing.info/trac/ticket/1104] ArchiveComment Parameter for ZipTask
 * [https://www.phing.info/trac/ticket/1095] ReferenceExistsCondition returns true for all UnknownElements
 * [https://www.phing.info/trac/ticket/1089] phing -l is listing imported targets twice
 * [https://www.phing.info/trac/ticket/1086] Support for running on HHVM
 * [https://www.phing.info/trac/ticket/1084] pdepend task does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/1069] PHPUnitTask formatter does not create directory if specified "todir" does not exist
 * [https://www.phing.info/trac/ticket/1068] Phingcall and Import issues
 * [https://www.phing.info/trac/ticket/1040] Composer task has no documentation
 * [https://www.phing.info/trac/ticket/1012] SymlinkTaks overwrite fails if target doesn't exist
 * [https://www.phing.info/trac/ticket/965] includePathTask: Allow appending and replacing
 * [https://www.phing.info/trac/ticket/945] several phpunit task problems
 * [https://www.phing.info/trac/ticket/930] Attribute logoutput to property task
 * [https://www.phing.info/trac/ticket/796] Can't delete all subdirectories without directory itself
 * [https://www.phing.info/trac/ticket/441] Reformat Phing source code to PSR-2

Jul. 18, 2014 - Phing 2.8.2
---------------------------

This patch release fixes two regressions.

 * [https://www.phing.info/trac/ticket/1119] #1111 breaks PHPLint task
 * [https://www.phing.info/trac/ticket/1118] Property "X" was circularly defined.

Jul. 1, 2014 - Phing 2.8.1
--------------------------

This patch release fixes a regression preventing Phing from
being used on machines where PEAR is not installed, as well
as another (unrelated) issue.

 * [https://www.phing.info/trac/ticket/1114] PHP Fatal Error using Phing on machines without PEAR
 * [https://www.phing.info/trac/ticket/1111] setting PhpLintTask interpreter

Jun. 30, 2014 - Phing 2.8.0
---------------------------

New or improved functionality:

 * The rsync task can now handle remote connections without specifying a username
 * The rsync task now creates remote directories as needed by default
 * Support for PHP MD 2.*
 * Various tasks now support dependencies loaded through composer
 * AutoloaderTask added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1108] pdosqlexec doesn't throw exception for the non-first SQL instruction
 * [https://www.phing.info/trac/ticket/1106] Add .git and associated files to defaultexcludes attribute
 * [https://www.phing.info/trac/ticket/1105] PHPUnitTask: attributes 'groups' and 'excludeGroups' not documented
 * [https://www.phing.info/trac/ticket/1102] Phing is not compatible with PHPMD 2.0.0 beta
 * [https://www.phing.info/trac/ticket/1101] Add (optional) external deps to suggest section in composer.json
 * [https://www.phing.info/trac/ticket/1100] Add composer / PHAR installation instructions to README & web pages
 * [https://www.phing.info/trac/ticket/1099] Allow loading of externals through composer [meta ticket]
 * [https://www.phing.info/trac/ticket/1091] Phing is not compatible with PHPUnit 4.x
 * [https://www.phing.info/trac/ticket/1090] PearPackageFileSet copies files with baseinstalldir incorrectly
 * [https://www.phing.info/trac/ticket/1085] Conditions section (5.8) does not correctly link to mentioned tasks
 * [https://www.phing.info/trac/ticket/1084] pdepend task does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/980] Support for .dist files
 * [https://www.phing.info/trac/ticket/975] Included JSmin has non-free license
 * [https://www.phing.info/trac/ticket/964] includePathTask: talk about appending/prepending

Feb. 13, 2014 - Phing 2.7.0
---------------------------

New or improved functionality:

 * Support for PHP CodeSniffer 1.5, PHP Copy&Paste Detector 2.0 and PHPLOC 2.0
 * Composer support for PHPCPD and PhpDocumentor tasks
 * Fixed / improved error handling in various places
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1083] PhpDocumentor2Task: add support for default package name
 * [https://www.phing.info/trac/ticket/1082] Tasks in root target are executed twice
 * [https://www.phing.info/trac/ticket/1081] Documentation of AvailableTask does not link to conditions page
 * [https://www.phing.info/trac/ticket/1078] IoncubeEncoderTask does not support PHP 5.4
 * [https://www.phing.info/trac/ticket/1073] Phing silently died, when cant read build.xml
 * [https://www.phing.info/trac/ticket/1070] PHPCS 1.5.0 breaks PHPCodeSniffer Task
 * [https://www.phing.info/trac/ticket/1064] Formatter 'brief' not implemented when using Unittest task
 * [https://www.phing.info/trac/ticket/1063] PHPCPD 2.0 breaks PHPCPD Task
 * [https://www.phing.info/trac/ticket/1062] AvailableTask throws exception when filepath contains duplicates
 * [https://www.phing.info/trac/ticket/1059] phing exits with return code 0 when there is unknown argument
 * [https://www.phing.info/trac/ticket/1057] pdo exception thrown from pdosqlexec not properly handled
 * [https://www.phing.info/trac/ticket/1056] filesyncTask: problem (error?) with verbose (-v) option
 * [https://www.phing.info/trac/ticket/1054] Missing or erroneous definition in phing-grammar.rng
 * [https://www.phing.info/trac/ticket/1053] Add composer support for phpdoc2 task
 * [https://www.phing.info/trac/ticket/1051] phing 2.6.1 - impossible upgrade
 * [https://www.phing.info/trac/ticket/1045] PHPLocTask broken with recent phploc updates
 * [https://www.phing.info/trac/ticket/1044] Using fileset in echo does not list subdirectories
 * [https://www.phing.info/trac/ticket/1042] Fix UnknownElement wrapping and configuring
 * [https://www.phing.info/trac/ticket/1035] phpcpd tasks does not find dependencies when installed by composer
 * [https://www.phing.info/trac/ticket/1034] Improving debuggability of errors in custom code
 * [https://www.phing.info/trac/ticket/1032] FileSync Port
 * [https://www.phing.info/trac/ticket/1030] JsMin task creates directories with 0700 permissions
 * [https://www.phing.info/trac/ticket/1028] Change visibility of FailTask variables
 * [https://www.phing.info/trac/ticket/1021] MailTask backend configuration
 * [https://www.phing.info/trac/ticket/1010] Invalid error about refid attribute when specifying multiple targets
 * [https://www.phing.info/trac/ticket/1009] certain liquibase tasks (rollback, tag and update) do not check return value
 * [https://www.phing.info/trac/ticket/994] Clarify pdoexec autocommit/transactions
 * [https://www.phing.info/trac/ticket/991] GitCommit: add fileset support
 * [https://www.phing.info/trac/ticket/984] Improve documentation about including custom tasks
 * [https://www.phing.info/trac/ticket/983] Selenium with PHPUnit: browser configurations are not processed
 * [https://www.phing.info/trac/ticket/978] svn switches: recursive
 * [https://www.phing.info/trac/ticket/976] phpunitreport: broken html for test suite names containing "/"
 * [https://www.phing.info/trac/ticket/650] Namespace support for extensions (PSR0 support)

Aug. 27, 2013 - Phing 2.6.1
---------------------------

This patch release fixes a regression when setting properties
in then/else blocks.

Note: the fix currently disables support for custom conditions,
full support will be restored in Phing 2.7.0.

 * [https://www.phing.info/trac/ticket/1041] Properties within then/else blocks are not expanded

Aug. 21, 2013 - Phing 2.6.0
---------------------------

New or improved functionality:

 * Docbook5 documentation is now the main documentation; output targets
   are 'hlhtml', 'chunkhtml', 'hlpdf', 'epub' and 'webhelp'
 * HttpRequest task supports POST request
 * PharPackage task supports PKCS#12 certificate stores
 * WikiPublish task was added
 * Smarty task is now compatible with Smarty 3
 * A new logger 'TargetLogger' was added, displaying the execution time for each target
 * Composer task and package were updated
 * More unit / regression tests added
 * Various bugs and documentation errors fixed

Additionally, the following Trac tickets (see www.phing.info) were fixed in this release:

 * [https://www.phing.info/trac/ticket/1037] PropertyTask docs is wrong
 * [https://www.phing.info/trac/ticket/1036] Error in ApplyTask->executeCommand()
 * [https://www.phing.info/trac/ticket/1029] PhpDocumentor2 task broken with latest phpdoc version
 * [https://www.phing.info/trac/ticket/1027] RegexpMapper uses deprecated PREG_REPLACE_EVAL
 * [https://www.phing.info/trac/ticket/1025] PHPLocTask fails when installed via composer
 * [https://www.phing.info/trac/ticket/1023] Argument 1 passed to IniFileTokenReader::setFile() must be an instance of PhingFile
 * [https://www.phing.info/trac/ticket/1020] [PHP Error] Illegal string offset 'filename' [line 149 of /usr/share/pear/phing/tasks/ext/ExtractBaseTask.php]
 * [https://www.phing.info/trac/ticket/1015] phing does not allow phpunit to echo
 * [https://www.phing.info/trac/ticket/1011] Problem with spaces in output redirection path
 * [https://www.phing.info/trac/ticket/1004] <gitcommit .../> does not work because task definition is missing in defaults.properties + another bug
 * [https://www.phing.info/trac/ticket/1003] 2 php syntax bugs in GitCommitTask
 * [https://www.phing.info/trac/ticket/1000] Make phing.phar work out of the box
 * [https://www.phing.info/trac/ticket/999]  phing-2.5.0.phar Can't load default task list
 * [https://www.phing.info/trac/ticket/993]  passthru will redirect stderr
 * [https://www.phing.info/trac/ticket/990]  Prompting for a property value when it is not set results in a repeated input message
 * [https://www.phing.info/trac/ticket/985]  Git Commit Task missing from docs
 * [https://www.phing.info/trac/ticket/981]  FileUtil::copyFile(): $preserveLastModified causes empty symlink target file
 * [https://www.phing.info/trac/ticket/970]  FileSyncTask missing from docbook5 documentation
 * [https://www.phing.info/trac/ticket/966]  phing unit tests nice on all platforms
 * [https://www.phing.info/trac/ticket/920]  Load phpdepend dependency only when they are used
 * [https://www.phing.info/trac/ticket/906]  Move to docbook5 documentation
 * [https://www.phing.info/trac/ticket/438]  pdosqlexec: add delimiterType=none (default), clarify delimiter documentation (was: pdosqlexec triggers segmentation fault)

Feb. 16, 2013 - Phing 2.5.0
---------------------------

This release addresses the following issues:

 * [https://www.phing.info/trac/ticket/979] svncommit: invalid switch ignoreexternals
 * [https://www.phing.info/trac/ticket/977] phpunit Task doesn't support @codeCoverageIgnore[...] comments
 * [https://www.phing.info/trac/ticket/972] SvnCopyTask: remove "force" from documentation
 * [https://www.phing.info/trac/ticket/971] TokenSource does not work
 * [https://www.phing.info/trac/ticket/969] PHPUnit task does not report diffs for failed assertions
 * [https://www.phing.info/trac/ticket/968] Proper handling of STDOUT and STDERR
 * [https://www.phing.info/trac/ticket/963] XSLT task fails with fatal error on PHP 5.4
 * [https://www.phing.info/trac/ticket/962] DbDeploy: infinite loop in case if directory not found
 * [https://www.phing.info/trac/ticket/961] DbDeploy: checkall output isn't informative
 * [https://www.phing.info/trac/ticket/960] Documentation of Dbdeploy task
 * [https://www.phing.info/trac/ticket/959] Bug in SvnListTask Version 2.4.14
 * [https://www.phing.info/trac/ticket/958] Property wrapped in if/then structure is not substituted by it's value
 * [https://www.phing.info/trac/ticket/954] Paths becoming part of S3 file names on Windows
 * [https://www.phing.info/trac/ticket/953] Add PHP extension check to Available Task
 * [https://www.phing.info/trac/ticket/952] Properly document how to load environment variables as properties
 * [https://www.phing.info/trac/ticket/951] S3Put throws "Source is not set" exception
 * [https://www.phing.info/trac/ticket/949] SymfonyConsoleTask improvements: checkreturn and output of command
 * [https://www.phing.info/trac/ticket/947] AvailableTask does not work on unix domain sockets
 * [https://www.phing.info/trac/ticket/946] <target hidden="true> is undocumented
 * [https://www.phing.info/trac/ticket/941] ZendGuardEncode under Windows 7
 * [https://www.phing.info/trac/ticket/937] DbDeployTask applied_by username is hardcoded and cannot be changed
 * [https://www.phing.info/trac/ticket/935] phpcodesniffertask does not work on CSS and JS files
 * [https://www.phing.info/trac/ticket/932] SshTask Methods Options
 * [https://www.phing.info/trac/ticket/921] JSL Lint Task - Halt on warning
 * [https://www.phing.info/trac/ticket/910] Add preservepermissions flag to copy task
 * [https://www.phing.info/trac/ticket/898] Add ApplyTask
 * [https://www.phing.info/trac/ticket/838] -D option doesn't work with a space after it
 * [https://www.phing.info/trac/ticket/599] Phar package does not work on Windows platforms

Nov. 29, 2012 - Phing 2.4.14
----------------------------

This release addresses the following issues:

  * [https://www.phing.info/trac/ticket/944] phing/phingdocs bad md5sum
  * [https://www.phing.info/trac/ticket/943] If task with "equals" directly in "project" tag does not work
  * [https://www.phing.info/trac/ticket/942] Typo in tasks/ext/dbdeploy/DbmsSyntaxOracle.php
  * [https://www.phing.info/trac/ticket/939] Add username/password to svn info/lastrevision/list/log task docs
  * [https://www.phing.info/trac/ticket/938] XSLT filter fails when libxslt security present in php

Starting from this version, Phing releases and release numbers will follow
the Semantic Versioning (www.semver.org) principle.

Nov. 20, 2012 - Phing 2.4.13
----------------------------

This release updates the composer package, adds a phploc task and improved
support for phpDocumentor 2 and IonCube 7, improves the unit tests,
clarifies the documentation in a number of places, and addresses
the following issues:

  * [https://www.phing.info/trac/ticket/933] PHPLoc 1.7 broken
  * [https://www.phing.info/trac/ticket/931] PHP_CodeSniffer throws errors with CodeSniffer 1.4.0
  * [https://www.phing.info/trac/ticket/929] Can not pass empty string (enclosed in double quotes) as exec task argument
  * [https://www.phing.info/trac/ticket/928] Fatal error with ZipTask when zip extension is not loaded
  * [https://www.phing.info/trac/ticket/927] PHPCPD upgrade breaks PHPCPD task
  * [https://www.phing.info/trac/ticket/926] FtpDeployTask: Missing features and patch for them (chmod and only change if different)
  * [https://www.phing.info/trac/ticket/925] Problem with spaces in error redirection path.
  * [https://www.phing.info/trac/ticket/924] Update to PEAR::VersionControl_SVN 0.5.0
  * [https://www.phing.info/trac/ticket/922] Introduce build file property that contains the build file's directory
  * [https://www.phing.info/trac/ticket/915] path with special characters does not delete
  * [https://www.phing.info/trac/ticket/909] Replace __DIR__
  * [https://www.phing.info/trac/ticket/905] Add filterchain support to the property task
  * [https://www.phing.info/trac/ticket/904] TarTask should raise error if zlib extension not installed
  * [https://www.phing.info/trac/ticket/903] Cannot redeclare class phpDocumentor\Bootstrap
  * [https://www.phing.info/trac/ticket/902] SvnBaseTask and subversion 1.7
  * [https://www.phing.info/trac/ticket/901] phpunitreport create html's classes files in wrong folder
  * [https://www.phing.info/trac/ticket/900] phpdoc2 example has error
  * [https://www.phing.info/trac/ticket/895] error in includepath when calling more than once
  * [https://www.phing.info/trac/ticket/893] Phing will run bootstrap before first task but clean up autoloader before second task
  * [https://www.phing.info/trac/ticket/892] Concatenate property lines ending with backslash
  * [https://www.phing.info/trac/ticket/891] Symfony console task: space within the arguments, not working on windows
  * [https://www.phing.info/trac/ticket/890] Allow custom child elements
  * [https://www.phing.info/trac/ticket/888] Documentation error for CvsTask setfailonerror
  * [https://www.phing.info/trac/ticket/886] Error throwing in PDOSQLExecTask breaking trycatch
  * [https://www.phing.info/trac/ticket/884] svnlist fails on empty directories
  * [https://www.phing.info/trac/ticket/882] Dbdeploy does not retrieve changelog number with oracle
  * [https://www.phing.info/trac/ticket/881] Silent fail on delete tasks
  * [https://www.phing.info/trac/ticket/880] Add phploc task
  * [https://www.phing.info/trac/ticket/867] phpcpd task should check external dep in main()
  * [https://www.phing.info/trac/ticket/866] Code coverage not showing "not executed" lines
  * [https://www.phing.info/trac/ticket/863] MoveTask ignores fileset
  * [https://www.phing.info/trac/ticket/845] GrowlNotifyTask to be notified on long-task when they are finished
  * [https://www.phing.info/trac/ticket/813] Allow custom conditions
  * [https://www.phing.info/trac/ticket/751] Allow loading of phpunit.xml in phpunit task
  * [https://www.phing.info/trac/ticket/208] ReplaceRegexp problem with newline as replace string

Apr. 6, 2012 - Phing 2.4.12
---------------------------

  * [https://www.phing.info/trac/ticket/877] Add 'level' attribute to resolvepath task
  * [https://www.phing.info/trac/ticket/876] JslLint Task is_executable() broken
  * [https://www.phing.info/trac/ticket/874] ParallelTask.php is not PHP 5.2 compatible
  * [https://www.phing.info/trac/ticket/860] SvnBaseTask: getRecursive
  * [https://www.phing.info/trac/ticket/539] Custom build log mailer
  * [https://www.phing.info/trac/ticket/406] an ability to turn phpLint verbose ON and OFF

Apr. 4, 2012 - Phing 2.4.11
---------------------------

  * [https://www.phing.info/trac/ticket/870] Can't find ParallelTask.php

Apr. 3, 2012 - Phing 2.4.10
---------------------------

  * [https://www.phing.info/trac/ticket/872] ReplaceTokens can't work with '/' char
  * [https://www.phing.info/trac/ticket/870] Can't find ParallelTask.php
  * [https://www.phing.info/trac/ticket/868] Git Clone clones into wrong directory
  * [https://www.phing.info/trac/ticket/865] static call to a non-static function PhingFile.php::getTempdir()
  * [https://www.phing.info/trac/ticket/854] PropertyTask with file. Can't use a comment delimiter in the value.
  * [https://www.phing.info/trac/ticket/853] PHP Error with HttpGetTask
  * [https://www.phing.info/trac/ticket/852] Several minor errors in documentation of core tasks
  * [https://www.phing.info/trac/ticket/851] RNG grammar hasn't been updated to current version
  * [https://www.phing.info/trac/ticket/850] Typo in documentation - required attributes for project
  * [https://www.phing.info/trac/ticket/849] Symfony 2 Console Task
  * [https://www.phing.info/trac/ticket/847] Add support for RNG grammar in task XmlLint
  * [https://www.phing.info/trac/ticket/846] RNG grammar is wrong for task 'foreach'
  * [https://www.phing.info/trac/ticket/844] symlink task - overwrite not working
  * [https://www.phing.info/trac/ticket/843] "verbose" option should print fileset/filelist filenames before execution, not afterwards
  * [https://www.phing.info/trac/ticket/840] Prevent weird bugs: raise warning when a target tag contains no ending tag
  * [https://www.phing.info/trac/ticket/835] JSL-Check faulty
  * [https://www.phing.info/trac/ticket/834] ExecTask documentation has incorrect escape attribute default value
  * [https://www.phing.info/trac/ticket/833] Exec task args with special characters cannot be escaped
  * [https://www.phing.info/trac/ticket/828] SelectorUtils::matchPath matches **/._* matches dir/file._name
  * [https://www.phing.info/trac/ticket/820] Type selector should treat symlinks to directories as such
  * [https://www.phing.info/trac/ticket/790] Make it easy to add new inherited types to phing: Use addFileset instead of createFileset
  * [https://www.phing.info/trac/ticket/772] Support for filelist in UpToDateTask
  * [https://www.phing.info/trac/ticket/671] fix CvsTask documentation
  * [https://www.phing.info/trac/ticket/587] More detailed backtrace in debug mode (patch)
  * [https://www.phing.info/trac/ticket/519] Extend mail task to include attachments
  * [https://www.phing.info/trac/ticket/419] schema file for editors and validation
  * [https://www.phing.info/trac/ticket/334] Run a task on BuildException

Dec. 29, 2011 - Phing 2.4.9
---------------------------

  * [https://www.phing.info/trac/ticket/837] PHPMDTask should check external dep in main()
  * [https://www.phing.info/trac/ticket/836] DocBlox task breaks with version 0.17.0: function getThemesPath not found
  * [https://www.phing.info/trac/ticket/831] dbdeploy undo script SQL is not formatted correctly
  * [https://www.phing.info/trac/ticket/822] rSTTask: add debug statement when creating target directory
  * [https://www.phing.info/trac/ticket/821] phingcall using a lot of memory
  * [https://www.phing.info/trac/ticket/819] Documentation for SvnUpdateTask is outdated
  * [https://www.phing.info/trac/ticket/818] [patch] Add overwrite option to Symlink task
  * [https://www.phing.info/trac/ticket/817] Adding the "trust-server-cert" option to SVN tasks
  * [https://www.phing.info/trac/ticket/816] Fix notice in SimpleTestXmlResultFormatter
  * [https://www.phing.info/trac/ticket/811] phpunitreport path fails on linux
  * [https://www.phing.info/trac/ticket/810] AvailableTask resolving symbolic links
  * [https://www.phing.info/trac/ticket/807] SVN tasks do not always show error message
  * [https://www.phing.info/trac/ticket/795] Untar : allow overwriting of newer files when extracting
  * [https://www.phing.info/trac/ticket/782] PharTask is very slow for big project
  * [https://www.phing.info/trac/ticket/776] Add waitFor task
  * [https://www.phing.info/trac/ticket/736] Incompatibility when copying from Windows to Linux on ScpTask
  * [https://www.phing.info/trac/ticket/709] talk about invalid property values
  * [https://www.phing.info/trac/ticket/697] More descriptive error messages in PharPackageTask
  * [https://www.phing.info/trac/ticket/674] Properties: global or local in tasks?
  * [https://www.phing.info/trac/ticket/653] Allow ChownTask to change only group
  * [https://www.phing.info/trac/ticket/619] verbose level in ExpandPropertiesFilter

Nov. 2, 2011 - Phing 2.4.8
--------------------------

  * [https://www.phing.info/trac/ticket/814] Class 'PHPCPD_Log_XML' not found in /home/m/www/elvis/vendor/phpcpd/PHPCPD/Log/XML/PMD.php on line 55
  * [https://www.phing.info/trac/ticket/812] Fix PHPUnit 3.6 / PHP_CodeCoverage 1.1.0 compatibility
  * [https://www.phing.info/trac/ticket/808] Bad example for the <or> selector
  * [https://www.phing.info/trac/ticket/805] phing executable has bug in ENV/PHP_COMMAND
  * [https://www.phing.info/trac/ticket/804] PhpUnitTask overwrites autoload stack
  * [https://www.phing.info/trac/ticket/801] PhpCodeSnifferTask doesn't pass files encoding to PHP_CodeSniffer
  * [https://www.phing.info/trac/ticket/800] CoverageReportTask fails with "runtime error" on PHP 5.4.0beta1
  * [https://www.phing.info/trac/ticket/799] DbDeploy does not support pdo-dblib
  * [https://www.phing.info/trac/ticket/798] ReplaceTokensWithFile - postfix attribute ignored
  * [https://www.phing.info/trac/ticket/797] PhpLintTask performance improvement
  * [https://www.phing.info/trac/ticket/794] Fix rSTTask to avoid the need of PEAR every time
  * [https://www.phing.info/trac/ticket/793] Corrected spelling of name
  * [https://www.phing.info/trac/ticket/792] EchoTask: Fileset support
  * [https://www.phing.info/trac/ticket/789] rSTTask unittests fix
  * [https://www.phing.info/trac/ticket/788] rSTTask documentation: fix examples
  * [https://www.phing.info/trac/ticket/787] Add pearPackageFileSet type
  * [https://www.phing.info/trac/ticket/785] method execute doesn't exists in CvsTask.php
  * [https://www.phing.info/trac/ticket/784] Refactor DocBlox task to work with DocBlox 0.14+
  * [https://www.phing.info/trac/ticket/783] SvnExportTask impossible to export current version from working copy
  * [https://www.phing.info/trac/ticket/779] phplint task error summary doesn't display the errors
  * [https://www.phing.info/trac/ticket/775] ScpTask: mis-leading error message if 'host' attribute is not set
  * [https://www.phing.info/trac/ticket/772] Support for filelist in UpToDateTask
  * [https://www.phing.info/trac/ticket/770] Keep the RelaxNG grammar in sync with the code/doc
  * [https://www.phing.info/trac/ticket/707] Writing Tasks/class properties: taskname not correctly used
  * [https://www.phing.info/trac/ticket/655] PlainPHPUnitResultFormatter does not display errors if @dataProvider was used
  * [https://www.phing.info/trac/ticket/578] [PATCH] Add mapper support to ForeachTask
  * [https://www.phing.info/trac/ticket/552] 2 validargs to input task does not display defaults correctly

Aug. 19, 2011 - Phing 2.4.7.1
-----------------------------

This is a hotfix release.

  * [https://www.phing.info/trac/ticket/774] Fix PHP 5.3 dependency in CoverageReportTask
  * [https://www.phing.info/trac/ticket/773] Fix for Ticket #744 breaks PHPCodeSnifferTask's nested formatters

Aug. 18, 2011 - Phing 2.4.7
---------------------------

This release fixes and improves several tasks (particularly the DocBlox
task), adds OCI/ODBC support to the dbdeploy task and introduces
a task to render reStructuredText.

  * [https://www.phing.info/trac/ticket/771] Undefined offset: 1 [line 204 of /usr/share/php/phing/tasks/ext/JslLintTask.php]
  * [https://www.phing.info/trac/ticket/767] PharPackageTask: metadata should not be required
  * [https://www.phing.info/trac/ticket/766] The DocBlox task does not load the markdown library.
  * [https://www.phing.info/trac/ticket/765] CoverageReportTask incorrectly considers dead code to be unexecuted
  * [https://www.phing.info/trac/ticket/762] Gratuitous unit test failures on Windows
  * [https://www.phing.info/trac/ticket/760] SelectorUtils::matchPath() directory matching broken
  * [https://www.phing.info/trac/ticket/759] DocBloxTask throws an error when using DocBlox 0.12.2
  * [https://www.phing.info/trac/ticket/757] Grammar error in ChmodTask documentation
  * [https://www.phing.info/trac/ticket/755] PharPackageTask Web/Cli stub path is incorrect
  * [https://www.phing.info/trac/ticket/754] ExecTask: <arg> support
  * [https://www.phing.info/trac/ticket/753] ExecTask: Unit tests and refactoring
  * [https://www.phing.info/trac/ticket/752] Declaration of Win32FileSystem::compare()
  * [https://www.phing.info/trac/ticket/750] Enable process isolation support in the PHPUnit task
  * [https://www.phing.info/trac/ticket/747] Improve "can't load default task list" message
  * [https://www.phing.info/trac/ticket/745] MkdirTask mode param mistake
  * [https://www.phing.info/trac/ticket/744] PHP_CodeSniffer formatter doesn't work with summary
  * [https://www.phing.info/trac/ticket/742] ExecTask docs: link os.name in os attribute
  * [https://www.phing.info/trac/ticket/741] ExecTask: missing docs for "output", "error" and "level"
  * [https://www.phing.info/trac/ticket/740] PHPMDTask: "InvalidArgumentException" with no globbed files.
  * [https://www.phing.info/trac/ticket/739] Making the jsMin suffix optional
  * [https://www.phing.info/trac/ticket/737] PHPCPDTask: omitting 'outfile' attribute with 'useFIle="false"'
  * [https://www.phing.info/trac/ticket/735] CopyTask can't copy broken symlinks when included in fileset
  * [https://www.phing.info/trac/ticket/733] DeleteTask cannot delete dangling symlinks
  * [https://www.phing.info/trac/ticket/731] Implement filepath support in Available Task
  * [https://www.phing.info/trac/ticket/720] rSTTask to render reStructuredText
  * [https://www.phing.info/trac/ticket/658] Add support to Oracle (OCI) in DbDeployTask
  * [https://www.phing.info/trac/ticket/580] ODBC in DbDeployTask
  * [https://www.phing.info/trac/ticket/553] copy task bails on symbolic links (filemtime)
  * [https://www.phing.info/trac/ticket/499] PDO cannot handle PL/Perl function creation statements in PostgreSQL

Jul. 12, 2011 - Phing 2.4.6
---------------------------

This release fixes a large number of issues, improves a number of tasks
and adds several new tasks (SVN log/list, DocBlox and LoadFile). 

  * [https://www.phing.info/trac/ticket/732] execTask fails to chdir if the chdir parameter is a symlink to a dir
  * [https://www.phing.info/trac/ticket/730] phpunitreport: styledir not required
  * [https://www.phing.info/trac/ticket/729] CopyTask fails when todir="" does not exist
  * [https://www.phing.info/trac/ticket/725] Clarify documentation for using AvailableTask as a condition
  * [https://www.phing.info/trac/ticket/723] setIni() fails with memory_limit not set in Megabytes
  * [https://www.phing.info/trac/ticket/719] TouchTask: file not required?
  * [https://www.phing.info/trac/ticket/718] mkdir: are parent directories created?
  * [https://www.phing.info/trac/ticket/715] Fix for mail task documentation
  * [https://www.phing.info/trac/ticket/712] expectSpecificBuildException fails to detect wrong exception message
  * [https://www.phing.info/trac/ticket/708] typo in docs: "No you can set"
  * [https://www.phing.info/trac/ticket/706] Advanced task example missing
  * [https://www.phing.info/trac/ticket/705] Missing links in Writing Tasks: Summary
  * [https://www.phing.info/trac/ticket/704] Case problem in "Writing Tasks" with setMessage
  * [https://www.phing.info/trac/ticket/703] missing links in "Package Imports"
  * [https://www.phing.info/trac/ticket/701] Setting more then two properties in command line not possible on windows
  * [https://www.phing.info/trac/ticket/699] Add loadfile task
  * [https://www.phing.info/trac/ticket/698] Add documentation for patternset element to user guide
  * [https://www.phing.info/trac/ticket/696] CoverageReportTask doesn't recognize UTF-8 source code
  * [https://www.phing.info/trac/ticket/695] phpunit Task doesn't support @codeCoverageIgnore[...] comments
  * [https://www.phing.info/trac/ticket/692] Class 'GroupTest' not found in /usr/share/php/phing/tasks/ext/simpletest/SimpleTestTask.php on line 158
  * [https://www.phing.info/trac/ticket/691] foreach doesn't work with filelists
  * [https://www.phing.info/trac/ticket/690] Support DocBlox
  * [https://www.phing.info/trac/ticket/689] Improve documentation about selectors
  * [https://www.phing.info/trac/ticket/688] SshTask Adding (+propertysetter, +displaysetter)
  * [https://www.phing.info/trac/ticket/685] SvnLogTask and SvnListTask
  * [https://www.phing.info/trac/ticket/682] Loading custom tasks should use the autoloading mechanism
  * [https://www.phing.info/trac/ticket/681] phpunit report does not work with a single testcase
  * [https://www.phing.info/trac/ticket/680] phpunitreport: make tables sortable
  * [https://www.phing.info/trac/ticket/679] IoncubeEncoderTask improved
  * [https://www.phing.info/trac/ticket/673] new listener HtmlColorLogger
  * [https://www.phing.info/trac/ticket/672] DbDeployTask::getDeltasFilesArray has undefined variable
  * [https://www.phing.info/trac/ticket/671] fix CvsTask documentation
  * [https://www.phing.info/trac/ticket/670] DirectoryScanner: add darcs to default excludes
  * [https://www.phing.info/trac/ticket/668] Empty Default Value Behaves Like the Value is not set
  * [https://www.phing.info/trac/ticket/667] Document how symbolic links and hidden files are treated in copy task
  * [https://www.phing.info/trac/ticket/663] __toString for register slots
  * [https://www.phing.info/trac/ticket/662] Hiding the command that is executed with "ExecTask"
  * [https://www.phing.info/trac/ticket/659] optionally skip version check in codesniffer task
  * [https://www.phing.info/trac/ticket/654] fileset not selecting folders
  * [https://www.phing.info/trac/ticket/652] PDOSQLExec task doesn't close the DB connection before throw an exception or at the end of the task.
  * [https://www.phing.info/trac/ticket/642] ERROR: option "-o" not known with phpcs version 1.3.0RC2 and phing/phpcodesniffer 2.4.4
  * [https://www.phing.info/trac/ticket/639] Add verbose mode for SCPTask
  * [https://www.phing.info/trac/ticket/635] ignored autocommit="false" in PDOTask?
  * [https://www.phing.info/trac/ticket/632] CoverageThresholdTask needs exclusion option/attribute
  * [https://www.phing.info/trac/ticket/626] Coverage threshold message is too detailed...
  * [https://www.phing.info/trac/ticket/616] PhpDocumentor prematurely checks for executable
  * [https://www.phing.info/trac/ticket/613] Would be nice to have -properties=<file> CLI option
  * [https://www.phing.info/trac/ticket/611] Attribute "title" is wanted in CoverageReportTask
  * [https://www.phing.info/trac/ticket/608] Tweak test failure message from PHPUnitTask
  * [https://www.phing.info/trac/ticket/591] PhpLintTask don't log all errors for each file
  * [https://www.phing.info/trac/ticket/563] Make PatchTask silent on FreeBSD
  * [https://www.phing.info/trac/ticket/546] Support of filelist in CodeCoverageTask
  * [https://www.phing.info/trac/ticket/527] pearpkg2: unable to specify different file roles
  * [https://www.phing.info/trac/ticket/521] jslint warning logger

Mar. 3, 2011 - Phing 2.4.5
--------------------------

This release fixes several issues, and reverts the changes
that introduced the ComponentHelper class.

  * [https://www.phing.info/trac/ticket/657] Wrong example of creating task in stable documentation.
  * [https://www.phing.info/trac/ticket/656] Many erratas on the "Getting Started"-page.
  * [https://www.phing.info/trac/ticket/651] Messages of ReplaceTokens should be verbose
  * [https://www.phing.info/trac/ticket/641] 2.4.4 packages contains .rej and .orig files in release tarball
  * [https://www.phing.info/trac/ticket/640] "phing -q" does not work: "Unknown argument: -q"
  * [https://www.phing.info/trac/ticket/634] php print() statement outputting to stdout
  * [https://www.phing.info/trac/ticket/624] PDOSQLExec fails with Fatal error: Class 'LogWriter' not found in [...]/PDOSQLExecFormatterElement
  * [https://www.phing.info/trac/ticket/623] 2.4.5RC1 requires PHPUnit erroneously
  * [https://www.phing.info/trac/ticket/621] PhpLintTask outputs all messages (info and errors) to same loglevel
  * [https://www.phing.info/trac/ticket/614] phpcodesniffer task changes Phing build working directory
  * [https://www.phing.info/trac/ticket/610] BUG: AdhocTaskdefTask fails when creating a task that extends from an existing task
  * [https://www.phing.info/trac/ticket/607] v 2.4.4 broke taskdef for tasks following PEAR naming standard
  * [https://www.phing.info/trac/ticket/603] Add support to PostgreSQL in DbDeployTask
  * [https://www.phing.info/trac/ticket/601] Add HTTP_Request2 to optional dependencies
  * [https://www.phing.info/trac/ticket/600] typo in ReplaceRegexpTask
  * [https://www.phing.info/trac/ticket/598] Wrong version for optional Services_Amazon_S3 dependency
  * [https://www.phing.info/trac/ticket/596] PhpDependTask no more compatible with PDepend since 0.10RC1
  * [https://www.phing.info/trac/ticket/593] Ssh/scp task: Move ssh2_connect checking from init to main
  * [https://www.phing.info/trac/ticket/564] command line "-D" switch not handled correctly under windows
  * [https://www.phing.info/trac/ticket/544] Wrong file set when exclude test/**/** is used

Dec. 2, 2010 - Phing 2.4.4
--------------------------

This release fixes several issues.

  * [https://www.phing.info/trac/ticket/595] FilterChain without ReplaceTokensWithFile creator
  * [https://www.phing.info/trac/ticket/594] Taskdef in phing 2.4.3 was broken!
  * [https://www.phing.info/trac/ticket/590] PhpLintTask don't flag files that can't be parsed as bad files
  * [https://www.phing.info/trac/ticket/589] Mail Task don't show recipients list on log
  * [https://www.phing.info/trac/ticket/588] Add (optional) dependency to VersionControl_Git and Services_Amazon_S3 packages
  * [https://www.phing.info/trac/ticket/585] Same line comments in property files are included in the property value
  * [https://www.phing.info/trac/ticket/570] XmlLintTask - check well-formedness only
  * [https://www.phing.info/trac/ticket/568] Boolean properties get incorrectly expanded
  * [https://www.phing.info/trac/ticket/544] Wrong file set when exclude test/**/** is used
  * [https://www.phing.info/trac/ticket/536] DbDeployTask: Undo script wrongly generated

Nov. 12, 2010 - Phing 2.4.3
---------------------------

This release adds tasks to interface with Git and Amazon S3, adds support for PHPUnit 3.5,
and fixes numerous issues.

  * [https://www.phing.info/trac/ticket/583] UnixFileSystem::compare() is broken
  * [https://www.phing.info/trac/ticket/582] Add haltonerror attribute to copy/move tasks
  * [https://www.phing.info/trac/ticket/581] XmlProperty creating wrong properties
  * [https://www.phing.info/trac/ticket/577] SVN commands fail on Windows XP
  * [https://www.phing.info/trac/ticket/575] xmlproperty - misplaced xml attributes
  * [https://www.phing.info/trac/ticket/574] Task "phpcodesniffer" broken, no output
  * [https://www.phing.info/trac/ticket/572] ImportTask don't skipp file if optional is set to true
  * [https://www.phing.info/trac/ticket/560] [PATCH] Compatibility with PHPUnit 3.5.
  * [https://www.phing.info/trac/ticket/559] UpToDate not override value of property when target is called by phingcall
  * [https://www.phing.info/trac/ticket/555] STRICT Declaration of UnixFileSystem::getBooleanAttributes() should be compatible with that of FileSystem::getBooleanAttributes()
  * [https://www.phing.info/trac/ticket/554] Patch to force PhpDocumentor to log using phing
  * [https://www.phing.info/trac/ticket/551] SVN Switch Task
  * [https://www.phing.info/trac/ticket/550] Ability to convert encoding of files
  * [https://www.phing.info/trac/ticket/549] ScpTask doesn't finish the transfer properly
  * [https://www.phing.info/trac/ticket/547] The new attribute version does not work
  * [https://www.phing.info/trac/ticket/543] d51PearPkg2Task: Docs link wrong
  * [https://www.phing.info/trac/ticket/542] JslLintTask: wrap conf parameter with escapeshellarg
  * [https://www.phing.info/trac/ticket/537] Install documentation incorrect/incomplete
  * [https://www.phing.info/trac/ticket/536] DbDeployTask: Undo script wrongly generated
  * [https://www.phing.info/trac/ticket/534] Task for downloading a file through HTTP
  * [https://www.phing.info/trac/ticket/531] cachefile parameter of PhpLintTask also caches erroneous files
  * [https://www.phing.info/trac/ticket/530] XmlLintTask does not stop buid process when schema validation fails
  * [https://www.phing.info/trac/ticket/529] d51pearpkg2: setOptions() call does not check return value
  * [https://www.phing.info/trac/ticket/526] pearpkg2: extdeps and replacements mappings not documented
  * [https://www.phing.info/trac/ticket/525] pearpkg2: minimal version on dependency automatically set max and recommended
  * [https://www.phing.info/trac/ticket/524] pearpkg2: maintainers mapping does not support "active" tag
  * [https://www.phing.info/trac/ticket/520] Need SvnLastChangedRevisionTask to grab the last changed revision for the current working directory
  * [https://www.phing.info/trac/ticket/518] [PHP Error] file_put_contents(): Filename cannot be empty in phpcpdesniffer task
  * [https://www.phing.info/trac/ticket/513] Version tag doesn't increment bugfix portion of the version
  * [https://www.phing.info/trac/ticket/511] Properties not being set on subsequent sets.
  * [https://www.phing.info/trac/ticket/510] to show test name when testing fails
  * [https://www.phing.info/trac/ticket/501] formatter type "clover" of task "phpunit" doesn't generate coverage according to task "coverage-setup"
  * [https://www.phing.info/trac/ticket/488] FtpDeployTask is very silent, error messages are not clear
  * [https://www.phing.info/trac/ticket/455] Should be able to ignore a task when listing them from CLI
  * [https://www.phing.info/trac/ticket/369] Add Git Support

Jul. 28, 2010 - Phing 2.4.2
---------------------------

  * [https://www.phing.info/trac/ticket/509] Phing.php setIni() does not honor -1 as unlimited
  * [https://www.phing.info/trac/ticket/506] Patch to allow -D<option> with no "=<value>"
  * [https://www.phing.info/trac/ticket/503] PHP Documentor Task not correctly documented
  * [https://www.phing.info/trac/ticket/502] Add repository url support to SvnLastRevisionTask
  * [https://www.phing.info/trac/ticket/500] static function call in PHPCPDTask
  * [https://www.phing.info/trac/ticket/498] References to Core types page are broken
  * [https://www.phing.info/trac/ticket/496] __autoload not being called
  * [https://www.phing.info/trac/ticket/492] Add executable attribute in JslLint task
  * [https://www.phing.info/trac/ticket/489] PearPackage Task fatal error trying to process Fileset options
  * [https://www.phing.info/trac/ticket/487] Allow files in subdirectories in ReplaceTokensWithFile filter
  * [https://www.phing.info/trac/ticket/486] PHP Errors in PDOSQLExecTask
  * [https://www.phing.info/trac/ticket/485] ReplaceTokensWithFile filter does not allow HTML translation to be switched off
  * [https://www.phing.info/trac/ticket/484] Make handling of incomplete tests when logging XML configurable
  * [https://www.phing.info/trac/ticket/483] Bug in FileUtils::copyFile() on Linux - when using FilterChains, doesn't preserve attributes
  * [https://www.phing.info/trac/ticket/482] Bug in ChownTask with verbose set to false
  * [https://www.phing.info/trac/ticket/480] ExportPropertiesTask does not export all the initialized properties
  * [https://www.phing.info/trac/ticket/477] HttpRequestTask should NOT validate output if regex is not provided
  * [https://www.phing.info/trac/ticket/474] Bad Comparisons in FilenameSelector (possibly others)
  * [https://www.phing.info/trac/ticket/473] CPanel can't read Phing's Zip Files
  * [https://www.phing.info/trac/ticket/472] Add a multiline option to regex replace filter
  * [https://www.phing.info/trac/ticket/471] ChownTask throws exception if group is given
  * [https://www.phing.info/trac/ticket/468] CopyTask does not accept a FileList as only source of files
  * [https://www.phing.info/trac/ticket/467] coverage of abstract class/method is always ZERO
  * [https://www.phing.info/trac/ticket/466] incomplete logging in coverage-threshold
  * [https://www.phing.info/trac/ticket/465] PatchTask should support more options
  * [https://www.phing.info/trac/ticket/463] Broken Links in coverage report
  * [https://www.phing.info/trac/ticket/461] version tag in project node

Mar. 10, 2010 - Phing 2.4.1
---------------------------

  * [https://www.phing.info/trac/ticket/460] FtpDeployTask error
  * [https://www.phing.info/trac/ticket/458] PHPCodeSniffer Task throws Exceptions
  * [https://www.phing.info/trac/ticket/456] Fileset's dir should honor expandsymboliclinks
  * [https://www.phing.info/trac/ticket/449] ZipTask creates ZIP file but doesn't set file/dir attributes
  * [https://www.phing.info/trac/ticket/448] PatchTask
  * [https://www.phing.info/trac/ticket/447] SVNCopy task is not documented
  * [https://www.phing.info/trac/ticket/446] Add documentation describing phpdocext
  * [https://www.phing.info/trac/ticket/444] PhpCodeSnifferTask fails to generate a checkstyle-like output
  * [https://www.phing.info/trac/ticket/443] HttpRequestTask is very desirable
  * [https://www.phing.info/trac/ticket/442] public key support for scp and ssh tasks
  * [https://www.phing.info/trac/ticket/436] Windows phing.bat can't handle PHP paths with spaces
  * [https://www.phing.info/trac/ticket/435] Phing download link broken in bibliography
  * [https://www.phing.info/trac/ticket/433] Error in Documentation in Book under Writing a simple Buildfile
  * [https://www.phing.info/trac/ticket/432] would be nice to create CoverateThresholdTask
  * [https://www.phing.info/trac/ticket/431] integrate Phing with PHP Mess Detector and PHP_Depend
  * [https://www.phing.info/trac/ticket/430] FtpDeployTask is extremely un-verbose...
  * [https://www.phing.info/trac/ticket/428] Ability to specify the default build listener in build file
  * [https://www.phing.info/trac/ticket/426] SvnExport task documentation does not mention "revision" property
  * [https://www.phing.info/trac/ticket/421] ExportProperties class incorrectly named
  * [https://www.phing.info/trac/ticket/420] Typo in setExcludeGroups function of PHPUnitTask
  * [https://www.phing.info/trac/ticket/418] Minor improvement for PhpLintTask

Jan. 17, 2010 - Phing 2.4.0
---------------------------

  * [https://www.phing.info/trac/ticket/414] PhpLintTask: retrieving bad files
  * [https://www.phing.info/trac/ticket/413] PDOSQLExecTask does not recognize "delimiter" command
  * [https://www.phing.info/trac/ticket/411] PhpEvalTask calculation should not always returns anything
  * [https://www.phing.info/trac/ticket/410] Allow setting alias for Phar files as well as a custom stub
  * [https://www.phing.info/trac/ticket/384] Delete directories fails on '[0]' name

Dec. 17, 2009 - Phing 2.4.0 RC3
-------------------------------

  * [https://www.phing.info/trac/ticket/407] some error with svn info
  * [https://www.phing.info/trac/ticket/406] an ability to turn phpLint verbose ON and OFF
  * [https://www.phing.info/trac/ticket/405] I can't get a new version of Phing through PEAR
  * [https://www.phing.info/trac/ticket/402] Add fileset/filelist support to scp tasks
  * [https://www.phing.info/trac/ticket/401] PHPUnitTask 'summary' formatter produces a long list of results
  * [https://www.phing.info/trac/ticket/400] Support for Clover coverage XML
  * [https://www.phing.info/trac/ticket/399] PhpDocumentorExternal stops in method constructArguments
  * [https://www.phing.info/trac/ticket/398] Error using ResolvePath on Windows
  * [https://www.phing.info/trac/ticket/397] DbDeployTask only looks for -- //@UNDO (requires space)
  * [https://www.phing.info/trac/ticket/396] PDOSQLExecTask requires both fileset and filelist, rather than either or
  * [https://www.phing.info/trac/ticket/395] PharPackageTask fails to compress files
  * [https://www.phing.info/trac/ticket/394] Fix differences in zip and tar tasks
  * [https://www.phing.info/trac/ticket/393] prefix parameter for tar task
  * [https://www.phing.info/trac/ticket/391] Docs: PharPackageTask 'compress' attribute wrong
  * [https://www.phing.info/trac/ticket/389] Code coverage shows incorrect results Part2
  * [https://www.phing.info/trac/ticket/388] Beautify directory names in zip archives
  * [https://www.phing.info/trac/ticket/387] IoncubeEncoderTask noshortopentags
  * [https://www.phing.info/trac/ticket/386] PhpCpd output to screen
  * [https://www.phing.info/trac/ticket/385] Directory ignored in PhpCpdTask.php
  * [https://www.phing.info/trac/ticket/382] Add prefix parameter to ZipTask
  * [https://www.phing.info/trac/ticket/381] FtpDeployTask: invalid default transfer mode
  * [https://www.phing.info/trac/ticket/380] How to use PhpDocumentorExternalTask
  * [https://www.phing.info/trac/ticket/379] PHPUnit error handler issue
  * [https://www.phing.info/trac/ticket/378] PHPUnit task bootstrap file included too late
  * [https://www.phing.info/trac/ticket/377] Code coverage shows incorrect results
  * [https://www.phing.info/trac/ticket/376] ReplaceToken boolean problems
  * [https://www.phing.info/trac/ticket/375] error in docs for echo task
  * [https://www.phing.info/trac/ticket/373] grammar errors
  * [https://www.phing.info/trac/ticket/372] Use E_DEPRECATED
  * [https://www.phing.info/trac/ticket/367] Can't build simple build.xml file
  * [https://www.phing.info/trac/ticket/361] Bug in PHPCodeSnifferTask
  * [https://www.phing.info/trac/ticket/360] &amp;&amp; transfers into & in new created task
  * [https://www.phing.info/trac/ticket/309] startdir and 'current directory' not the same when build.xml not in current directory
  * [https://www.phing.info/trac/ticket/268] Patch - xmlproperties Task
  * [https://www.phing.info/trac/ticket/204] Resolve task class names with PEAR/ZEND/etc. naming convention
  * [https://www.phing.info/trac/ticket/137] Excluded files may be included in Zip/Tar tasks

Oct. 20, 2009 - Phing 2.4.0 RC2
-------------------------------

  * [https://www.phing.info/trac/ticket/370] Fatal error: Cannot redeclare class PHPUnit_Framework_TestSuite
  * [https://www.phing.info/trac/ticket/366] Broken link in "Getting Started/More Complex Buildfile"
  * [https://www.phing.info/trac/ticket/365] Phing 2.4rc1 via pear is not usable
  * [https://www.phing.info/trac/ticket/364] 2.4.0-rc1 download links broken
  * [https://www.phing.info/trac/ticket/363] PHPUnit task fails with formatter type 'xml'
  * [https://www.phing.info/trac/ticket/359] 403 for Documentation (User Guide) Phing HEAD
  * [https://www.phing.info/trac/ticket/355] PDOSQLExecTask should accept filelist subelement
  * [https://www.phing.info/trac/ticket/352] Add API documentation

Sep. 14, 2009 - Phing 2.4.0 RC1
-------------------------------

  * [https://www.phing.info/trac/ticket/362] Can't get phpunit code coverage to export as XML
  * [https://www.phing.info/trac/ticket/361] Bug in PHPCodeSnifferTask
  * [https://www.phing.info/trac/ticket/357] SvnLastRevisionTask fails when locale != EN
  * [https://www.phing.info/trac/ticket/356] Documentation for tasks Chmod and Chown
  * [https://www.phing.info/trac/ticket/349] JslLint task fails to escape shell argument
  * [https://www.phing.info/trac/ticket/347] PHPUnit / Coverage tasks do not deal with bootstrap code
  * [https://www.phing.info/trac/ticket/344] Phing ignores public static array named $browsers in Selenium tests
  * [https://www.phing.info/trac/ticket/342] custom-made re-engine in SelectorUtils is awful slow
  * [https://www.phing.info/trac/ticket/339] PHAR signature setting
  * [https://www.phing.info/trac/ticket/336] Use intval to loop through files
  * [https://www.phing.info/trac/ticket/333] XmlLogger doesn't ensure proper ut8 encoding of log messages
  * [https://www.phing.info/trac/ticket/332] Conditions: uptodate does not work
  * [https://www.phing.info/trac/ticket/331] UpToDateTask documentation says that nested FileSet tags are allowed
  * [https://www.phing.info/trac/ticket/330] "DirectoryScanner cannot find a folder/file named ""0"" (zero)"
  * [https://www.phing.info/trac/ticket/326] Add revision to svncheckout and svnupdate
  * [https://www.phing.info/trac/ticket/325] "<filterchain id=""xxx""> and <filterchain refid=""xxx""> don't work"
  * [https://www.phing.info/trac/ticket/322] phpdoc task not parsing and including  RIC files in documentation output
  * [https://www.phing.info/trac/ticket/319] Simpletest sometimes reports an undefined variable
  * [https://www.phing.info/trac/ticket/317] PhpCodeSnifferTask lacks of haltonerror and haltonwarning attributes
  * [https://www.phing.info/trac/ticket/316] Make haltonfailure attribute for ZendCodeAnalyzerTask
  * [https://www.phing.info/trac/ticket/312] SimpleTestXMLResultFormatter
  * [https://www.phing.info/trac/ticket/311] Fileset support for the TouchTask?
  * [https://www.phing.info/trac/ticket/307] Replaceregexp filter works in Copy task but not Move task
  * [https://www.phing.info/trac/ticket/306] Command-line option to output the <target> description attribute text
  * [https://www.phing.info/trac/ticket/303] Documentation of Task Tag SimpleTest
  * [https://www.phing.info/trac/ticket/300] ExecTask should return command output as a property (different from passthru)
  * [https://www.phing.info/trac/ticket/299] PhingCall crashes if an AdhocTask is defined
  * [https://www.phing.info/trac/ticket/292] Svn copy task
  * [https://www.phing.info/trac/ticket/290] Add facility for setting resolveExternals property of DomDocument object in XML related tasks
  * [https://www.phing.info/trac/ticket/289] Undefined property in XincludeFilter class
  * [https://www.phing.info/trac/ticket/282] Import Task fix/improvement
  * [https://www.phing.info/trac/ticket/280] Add Phar support (task) to Phing
  * [https://www.phing.info/trac/ticket/279] Add documentation to PHK package task
  * [https://www.phing.info/trac/ticket/278] Add PHK package task
  * [https://www.phing.info/trac/ticket/277] PhpCodeSnifferTask has mis-named class, patch included
  * [https://www.phing.info/trac/ticket/273] PHPUnit 3.3RC1 error in phpunit task adding files to filter
  * [https://www.phing.info/trac/ticket/270] [patch] ReplaceRegExp
  * [https://www.phing.info/trac/ticket/269] Allow properties to be recursively named.
  * [https://www.phing.info/trac/ticket/263] phpunit code coverage file format change
  * [https://www.phing.info/trac/ticket/262] Archive_Zip fails to extract on Windows
  * [https://www.phing.info/trac/ticket/261] UnZip task reports success on failure on Windows
  * [https://www.phing.info/trac/ticket/259] Unneeded warning in Untar task
  * [https://www.phing.info/trac/ticket/256] Ignore dead code in code coverage
  * [https://www.phing.info/trac/ticket/254] Add extra debug resultformatter to the simpletest task
  * [https://www.phing.info/trac/ticket/252] foreach on a fileset
  * [https://www.phing.info/trac/ticket/248] Extend taskdef task to allow property file style imports
  * [https://www.phing.info/trac/ticket/247] New task: Import
  * [https://www.phing.info/trac/ticket/246] Phing test brocken but no failure entry if test case class has no test method
  * [https://www.phing.info/trac/ticket/245] TAR task
  * [https://www.phing.info/trac/ticket/243] Delete task won't delete all files
  * [https://www.phing.info/trac/ticket/240] phing test successful while phpunit test is broken
  * [https://www.phing.info/trac/ticket/233] Separate docs from phing package
  * [https://www.phing.info/trac/ticket/231] File::exists() returns false on *existing* but broken symlinks
  * [https://www.phing.info/trac/ticket/229] CopyTask shoul accept filelist subelement
  * [https://www.phing.info/trac/ticket/226] <move> task doesn't support filters
  * [https://www.phing.info/trac/ticket/222] Terminal output disappears and/or changes color
  * [https://www.phing.info/trac/ticket/221] Support for copying symlinks as is
  * [https://www.phing.info/trac/ticket/212] Make file perms configurable in copy task
  * [https://www.phing.info/trac/ticket/209] Cache the results of PHPLintTask so as to not check unmodified files
  * [https://www.phing.info/trac/ticket/187] "ExecTask attribute ""passthru"" to make use of the PHP function ""passthru"""
  * [https://www.phing.info/trac/ticket/21] svn tasks doesn't work

Dec. 8, 2008 - Phing 2.3.3
--------------------------

  * [https://www.phing.info/trac/ticket/314] <phpunit> task does not work
  * [https://www.phing.info/trac/ticket/313] Incorrect PhpDoc package of SimpleTestResultFormatter
  * [https://www.phing.info/trac/ticket/302] Incorrect error detecting in XSLT filter
  * [https://www.phing.info/trac/ticket/293] Contains condition fails on case-insensitive checks
  * [https://www.phing.info/trac/ticket/291] The release package is not the one as the version(2.3.2) suppose to be

Oct. 16, 2008 - Phing 2.3.2
---------------------------

  * [https://www.phing.info/trac/ticket/296] Problem with the Phing plugin with Hudson CI Tool
  * [https://www.phing.info/trac/ticket/288] Comment syntax for dbdeploy violates standard

Oct. 16, 2008 - Phing 2.3.1
---------------------------

  * [https://www.phing.info/trac/ticket/287] DateSelector.php bug
  * [https://www.phing.info/trac/ticket/286] dbdeploy failes with MySQL strict mode
  * [https://www.phing.info/trac/ticket/285] Syntax error in dbdeploy task
  * [https://www.phing.info/trac/ticket/284] XSL Errors in coverage-report task
  * [https://www.phing.info/trac/ticket/275] AnsiColorLogger should not be final
  * [https://www.phing.info/trac/ticket/274] PHPUnit 3.3RC1 incompatibility with code coverage
  * [https://www.phing.info/trac/ticket/272] Using CDATA with ReplaceTokens values
  * [https://www.phing.info/trac/ticket/271] Warning on iterating over empty keys
  * [https://www.phing.info/trac/ticket/264] Illeal use of max() with empty array
  * [https://www.phing.info/trac/ticket/260] Error processing reults: SQLSTATE [HY000]: General error: 2053 when executing inserts or create statements.
  * [https://www.phing.info/trac/ticket/258] getPhingVersion + printVersion should be public static
  * [https://www.phing.info/trac/ticket/255] Timestamp in Phing Properties for Echo etc
  * [https://www.phing.info/trac/ticket/253] CCS nav bug on PHING.info site
  * [https://www.phing.info/trac/ticket/251] debug statement in Path datatype for DirSet
  * [https://www.phing.info/trac/ticket/249] See failed tests in console
  * [https://www.phing.info/trac/ticket/244] Phing pear install nor working
  * [https://www.phing.info/trac/ticket/242] Log incomplete and skipped tests for phpunit3
  * [https://www.phing.info/trac/ticket/241] FtpDeployTask reports FTP port as FTP server on error
  * [https://www.phing.info/trac/ticket/239] ExecTask shows no output from running command
  * [https://www.phing.info/trac/ticket/238] Bug in SummaryPHPUnit3ResultFormatter
  * [https://www.phing.info/trac/ticket/237] Several PHP errors in XSLTProcessor
  * [https://www.phing.info/trac/ticket/236] Do not show passwords for svn in log
  * [https://www.phing.info/trac/ticket/234] typo in foreach task documentation
  * [https://www.phing.info/trac/ticket/230] Fatal error: Call to undefined method PHPUnit2_Framework_TestResult::skippedCount() in /usr/local/lib/php/phing/tasks/ext/phpunit/PHPUnitTestRunner.php on line 120
  * [https://www.phing.info/trac/ticket/227] simpletestformaterelement bad require
  * [https://www.phing.info/trac/ticket/225] Missing Software Dependence in documentation
  * [https://www.phing.info/trac/ticket/224] Path class duplicates absolute path on subsequent path includes
  * [https://www.phing.info/trac/ticket/220] AnsiColorLogger colors cannot be changed by build.properties
  * [https://www.phing.info/trac/ticket/219] Add new chown task
  * [https://www.phing.info/trac/ticket/218] Clear support of PHPUnit versions
  * [https://www.phing.info/trac/ticket/217] Memory limit in phpdoc
  * [https://www.phing.info/trac/ticket/216] output messages about errors and warnings in JslLint task
  * [https://www.phing.info/trac/ticket/215] boolean attributes of task PhpCodeSniffer are wrong
  * [https://www.phing.info/trac/ticket/214] PhpCodeSnifferTask should be able to output file
  * [https://www.phing.info/trac/ticket/213] Error in documentation task related to copy task
  * [https://www.phing.info/trac/ticket/211] XSLT does not handle multiple testcase nodes for the same test method
  * [https://www.phing.info/trac/ticket/210] Reworked PhpDocumentorExternalTask
  * [https://www.phing.info/trac/ticket/208] ReplaceRegexp problem with newline as replace string
  * [https://www.phing.info/trac/ticket/207] PhpLintTask: optional use a different PHP interpreter
  * [https://www.phing.info/trac/ticket/206] Installation guide out of date (phing fails to run)
  * [https://www.phing.info/trac/ticket/205] AvailableTask::_checkResource ends up with an exception if resource isn't found.
  * [https://www.phing.info/trac/ticket/203] ExecTask returnProperty
  * [https://www.phing.info/trac/ticket/202] Add PHP_CodeSniffer task
  * [https://www.phing.info/trac/ticket/201] "Improve Phing's ability to work as an ""embedded"" process"
  * [https://www.phing.info/trac/ticket/200] Additional attribute for SvnUpdateTask
  * [https://www.phing.info/trac/ticket/199] Invalid error message in delete task when deleting directory fails.
  * [https://www.phing.info/trac/ticket/198] PDO SQL exec task unable to handle multi-line statements
  * [https://www.phing.info/trac/ticket/197] phing delete task sometimes fails to delete file that could be deleted
  * [https://www.phing.info/trac/ticket/195] SvnLastRevisionTask fails if Subversion is localized (Spanish)
  * [https://www.phing.info/trac/ticket/194] haltonincomplete attribute for phpunit task
  * [https://www.phing.info/trac/ticket/193] Manifest Task
  * [https://www.phing.info/trac/ticket/192] Error when skip test
  * [https://www.phing.info/trac/ticket/191] Akismet says content is spam
  * [https://www.phing.info/trac/ticket/190] Add test name in printsummary in PHPUnit task
  * [https://www.phing.info/trac/ticket/185] PHPUnit_MAIN_METHOD defined more than once
  * [https://www.phing.info/trac/ticket/184] PlainPHPUnit3ResultFormatter filteres test in stack trace
  * [https://www.phing.info/trac/ticket/183] Undefined variable in PhingTask.php
  * [https://www.phing.info/trac/ticket/182] Undefined variable in  SummaryPHPUnit3ResultFormatter
  * [https://www.phing.info/trac/ticket/181] PhingCallTask should call setHaltOnFailure
  * [https://www.phing.info/trac/ticket/179] Add documentation for TidyFilter
  * [https://www.phing.info/trac/ticket/178] printsummary doens work in PHP Unit task
  * [https://www.phing.info/trac/ticket/177] Only write ConfigurationExceptions to stdout
  * [https://www.phing.info/trac/ticket/176] Cleanup installation documentation.
  * [https://www.phing.info/trac/ticket/175] passing aarguments to phing
  * [https://www.phing.info/trac/ticket/169] Spurious PHP Error from XSLT Filter
  * [https://www.phing.info/trac/ticket/150] unable to include phpdocumentor.ini in PHPDoc-Task
  * [https://www.phing.info/trac/ticket/15] FTP upload task

Nov. 3, 2007 - Phing 2.3.0
--------------------------

  * [https://www.phing.info/trac/ticket/174] Add differentiation for build loggers that require explicit streams to be set
  * [https://www.phing.info/trac/ticket/173] Add 'value' alias to XSLTParam type.
  * [https://www.phing.info/trac/ticket/172] broken phpunit2-frames.xsl
  * [https://www.phing.info/trac/ticket/171] Allow results from selector to be loosely type matched to true/false
  * [https://www.phing.info/trac/ticket/170] SvnLastRevisionTask cannot get SVN revision number on single file
  * [https://www.phing.info/trac/ticket/168] XincludeFilter PHP Error
  * [https://www.phing.info/trac/ticket/167] Add new formatter support for PDOSQLExecTask
  * [https://www.phing.info/trac/ticket/166] Change CreoleTask to use <creole> tagname instead of <sql>
  * [https://www.phing.info/trac/ticket/165] Add support for PHPUnit_Framework_TestSuite subclasses in fileset of test classes
  * [https://www.phing.info/trac/ticket/164] Failed build results in empty log.xml
  * [https://www.phing.info/trac/ticket/163] Add stripwhitespace filter
  * [https://www.phing.info/trac/ticket/162] Add @pattern alias for @name in <fileset>
  * [https://www.phing.info/trac/ticket/161] phing/etc directory missing (breaking PHPUnit)
  * [https://www.phing.info/trac/ticket/157] Fatal error in PDOSQLExecTask when using filesets
  * [https://www.phing.info/trac/ticket/155] <delete> fails when it encounters symlink pointing to non-writable file
  * [https://www.phing.info/trac/ticket/154] Suggestion to add attribute to PDOSQLExecTask for fetch_style
  * [https://www.phing.info/trac/ticket/153] sqlite select failure
  * [https://www.phing.info/trac/ticket/152] result of PHP-Unit seems to be incorrect
  * [https://www.phing.info/trac/ticket/151] add group-option to PHPUnit-Task
  * [https://www.phing.info/trac/ticket/149] using TestSuites in fileset of PHPUnit-Task
  * [https://www.phing.info/trac/ticket/148] remove dependency to PEAR in PHPUnit-Task
  * [https://www.phing.info/trac/ticket/146] Illegal offset type PHP notice in CopyTask
  * [https://www.phing.info/trac/ticket/143] Example for PhpDocumentor task has typographical errors and a wrong attribute.
  * [https://www.phing.info/trac/ticket/142] SvnCheckout task only makes non-recursive checkouts.
  * [https://www.phing.info/trac/ticket/141] Add 'recursive' attribute to svncheckout task.
  * [https://www.phing.info/trac/ticket/136] Attribute os of ExecTask is not working
  * [https://www.phing.info/trac/ticket/135] add source file attribute for code coverage xml report
  * [https://www.phing.info/trac/ticket/133] Error in documenation: AppendTask
  * [https://www.phing.info/trac/ticket/129] Typo in documentation
  * [https://www.phing.info/trac/ticket/128] <pearpkg2> is missing in the doc completely
  * [https://www.phing.info/trac/ticket/127] Error in documentation
  * [https://www.phing.info/trac/ticket/126] Typo in documentation
  * [https://www.phing.info/trac/ticket/122] PearPackage2Task Replacements don't seem to work
  * [https://www.phing.info/trac/ticket/121] BUILD FAILED use JsLintTask
  * [https://www.phing.info/trac/ticket/119] PhpDocumentorTask fails when trying to use parsePrivate attribute.
  * [https://www.phing.info/trac/ticket/118] custom tasks have this->project == null
  * [https://www.phing.info/trac/ticket/117] CoverageSetupTask and autoloaders
  * [https://www.phing.info/trac/ticket/116] Test unit don't report notice or strict warnings
  * [https://www.phing.info/trac/ticket/110] "Add ""errorproperty"" attribute to PhpLintTask"
  * [https://www.phing.info/trac/ticket/107] SvnLastRevisionTask doesn't work with repositoryUrl
  * [https://www.phing.info/trac/ticket/106] "document ""haltonfailure"" attribute for phplint task"
  * [https://www.phing.info/trac/ticket/105] FileSystemUnix::normalize method: Improve handling
  * [https://www.phing.info/trac/ticket/97] delete dir and mkdir are incompatible
  * [https://www.phing.info/trac/ticket/92] Inconsistent newlines in PHP files
  * [https://www.phing.info/trac/ticket/91] Improve detection for PHPUnit3
  * [https://www.phing.info/trac/ticket/83] "XmlLogger improperly handling ""non-traditional"" buildfile execution paths"
  * [https://www.phing.info/trac/ticket/82] Error when use markTestIncomplete in test
  * [https://www.phing.info/trac/ticket/79] Allow escaped dots in classpaths
  * [https://www.phing.info/trac/ticket/78] (SVN doc) ${phing.version} and ${php.version} are different!
  * [https://www.phing.info/trac/ticket/77] taskdef doesn't support fileset
  * [https://www.phing.info/trac/ticket/76] Overhaul PhpDocumentor task
  * [https://www.phing.info/trac/ticket/75] files excluded by fileset end up in .tgz but not .zip
  * [https://www.phing.info/trac/ticket/74] Phing commandline args don't support quoting / spaces
  * [https://www.phing.info/trac/ticket/73] Semantical error in PhingFile::getParent()
  * [https://www.phing.info/trac/ticket/72] "Remove use of getProperty(""line.separator"") in favor of PHP_EOL"
  * [https://www.phing.info/trac/ticket/71] "Add ""-p"" alias for project help"
  * [https://www.phing.info/trac/ticket/70] Create Project class constants for log levels (replacing PROJECT_MSG_*)
  * [https://www.phing.info/trac/ticket/69] mkdir and delete tasks don't work properly together
  * [https://www.phing.info/trac/ticket/68] Xinclude filter
  * [https://www.phing.info/trac/ticket/67] Add PDO SQL execution task
  * [https://www.phing.info/trac/ticket/66] Incorrectly set PHP_CLASSPATH in phing.bat
  * [https://www.phing.info/trac/ticket/65] Convert all loggers/listeners to use streams
  * [https://www.phing.info/trac/ticket/64] Build listeners currently not working
  * [https://www.phing.info/trac/ticket/63] Configured -logger can get overridden
  * [https://www.phing.info/trac/ticket/62] phing.buildfile.dirname built-in property
  * [https://www.phing.info/trac/ticket/58] Path::listPaths() broken for DirSet objects.
  * [https://www.phing.info/trac/ticket/57] FileList.getListFile method references undefined variable
  * [https://www.phing.info/trac/ticket/56] TaskHandler passing incorrect param to ProjectConfigurator->configureId()
  * [https://www.phing.info/trac/ticket/53] _makeCircularException seems to have an infinite loop
  * [https://www.phing.info/trac/ticket/52] \<match>-syntax does not work correctly with preg_*()
  * [https://www.phing.info/trac/ticket/51] Cannot get phing to work with PHPUnit 3
  * [https://www.phing.info/trac/ticket/48] Supported PHPUnit2_Framework_TestSuite and PHPUnit2_Extensions_TestSetup sub-classes for the PHPUnit2Task and CoverageReportTask tasks
  * [https://www.phing.info/trac/ticket/33] Implement changes to use PHPUnit2 3.0 code coverage information
  * [https://www.phing.info/trac/ticket/22] Description about integrating into CruiseControl

Aug. 21, 2006 - Phing 2.2.0
---------------------------

  * Refactored parser to support many tags as children of base <project> tag (HL)
  * Added new IfTask (HL)
  * Added "spawn" attribute to ExecTask (only applies to *nix)
  * Several bugfixes & behavior imporvements to ExecTask (HL, MR, Ben Gollmer)
  * Bugfixes & refactoring for SVNLastRevisionTask (MR, Knut Urdalen)
  * Fixed reference copy bug (HL, Matthias Pigulla)
  * Added SvnExportTask (MR)
  * Added support for FileList in DeleteTask. (HL)
  * Added support for using setting Properties using CDATA value of <property> tag. (HL)
  * Added ReferenceExistsCondition (Matthias Pigulla)
  * Added Phing::log() static method & integrated PHP error handling with Phing logging (HL)
  * Added new task to run the ionCube Encoder (MR)
  * Added new HTML Tidy filter (HL)
  * Added PhpLintTask (Knut Urdalen)
  * Added XmlLintTask (Knut Urdalen)
  * Added ZendCodeAnalyzerTask (Knut Urdalen)
  * Removed CoverageFormatter class (MR). NOTE: This changes the usage of the collection of PHPUnit2 code coverage reports, see the updated documentation for the CoverageSetupTask
  * Added Unzip and Untar tasks contributed by Joakim Bodin
  * [https://www.phing.info/trac/ticket/8], [49] Fixed bugs in TarTask related to including empty directories (HL)
  * [https://www.phing.info/trac/ticket/44] Fixed bug related to copying empty dirs. (HL)
  * [https://www.phing.info/trac/ticket/32] Fixed PHPUnit2 tasks to work with PHPUnit2-3.0.0 (MR)
  * [https://www.phing.info/trac/ticket/31] Fixed bug with using PHPDocumentor 1.3.0RC6 (MR)
  * [https://www.phing.info/trac/ticket/43] Fixed top-level (no target) IfTask behavior (Matthias Pigulla)
  * [https://www.phing.info/trac/ticket/41] Removed some lingering E_STRICT errors, bugs with 5.1.x and PHP >= 5.0.5 (HL)
  * [https://www.phing.info/trac/ticket/25] Fixed 'phing' script to also run on non-bash unix /bin/sh 
  * Numerous documentation improvements by many members of the community (Thanks!)
  
Sept. 18, 2005 - Phing 2.1.1
----------------------------

  * Added support for specifying 4-char mask (e.g. 1777) to ChmodTask. (Hans Lellelid)
  * Added .svn files to default excludes in DirectoryScanner.
  * Updated PHPUnit2 BatchTest to use class detection and non-dot-path loader. (Michiel Rook)
  * Added support for importing non dot-path files (Michiel Rook)
  * Add better error message when build fails with exception (Hans Lellelid)
  * Fixed runtime error when errors were encountered in AppendTask (Hans Lellelid)

June 17, 2005 - Phing 2.1.0
---------------------------

  * Renamed File -> PhingFile to avoid namespace collisions (Michiel Rook)
  * Add ZipTask to create .zip files (Michiel Rook)
  * Removed redudant logging of build errors in Phing::start() (Michiel Rook)
  * Added tasks to execute PHPUnit2 testsuites and generate coverage and test reports. (Michiel Rook, Sebastian Bergmann)
  * Added SvnLastRevisionTask that stores the number of the last revision of a workingcopy in a property. (Michiel Rook)
  * Added MailTask that sends a message by mail() (Michiel Rook, contributed by Francois Harvey)
  * New IncludePathTask (<includepath/>) for adding values to PHP's include_path. (Hans Lellelid)
  * Fix to Phing::import() to *not* attempt to invoke __autoload() in class_exists() check. (Hans Lellelid)
  * Fixed AppendTask to allow use of only <fileset> as source. (Hans Lellelid)
  * Removed dependency on posix, by changing posix_uname to php_uname if needed. (Christian Stocker)
  * Fixed issues: (Michiel Rook)
  * [https://www.phing.info/trac/ticket/11] ExtendedFileStream does not work on Windows
  * [https://www.phing.info/trac/ticket/12] CoverageFormatter problem on Windows
  * [https://www.phing.info/trac/ticket/13] DOMElement warnings in PHPUnit2 tasks
  * [https://www.phing.info/trac/ticket/14] RuntimeException conflicts with SPL class
  * [https://www.phing.info/trac/ticket/15] It is not possible to execute it with PHP5.1
  * [https://www.phing.info/trac/ticket/16] Add Passthru option to ExecTask
  * [https://www.phing.info/trac/ticket/17] Blank list on foreach task will loop once
  * [https://www.phing.info/trac/ticket/19] Problem with <formatter outfile="...">
  * [https://www.phing.info/trac/ticket/20] Phpunit2report missing XSL stylesheets
  * [https://www.phing.info/trac/ticket/21] Warnings when output dir does not exist in PHPUnit2Report

Oct 16, 2004 - Phing 2.0.0
--------------------------

  * Minor fixes to make Phing run under E_STRICT/PHP5.
  * Fix to global/system properties not being set in project. (Matt Zandstra)
  * Fixes to deprecated return by reference issues w/ PHP5.0.0

June 8, 2004 - Phing 2.0.0b3
----------------------------

  * Brought up-to-date w/ PHP5.0.0RC3
  * Fixed several bugs in ForeachTask
  * Fixed runtime errors and incomplete inheriting of properties in PhingTask
  * Added <fileset> support to AppendTask

March 19, 2004 - Phing 2.0.0b2
------------------------------

  * Brought up-to-date w/ PHP5.0.0RC1 (Hans)
  * Fixed bug in seting XSLT params using XSLTask (Hans, Jeff Moss)
  * Fixed PHPUnit test framework for PHPUnit-2.0.0alpha3
  * Added "Adhoc" tasks, which allow for defining PHP task or type classes within the buildfile. (Hans)
  * Added PhpEvalTask which allows property values to be set to simple PHP evaluations or the results of function/method calls. (Hans)
  * Added new phing.listener.PearLogger listener (logger).  Also, the -logfile arg is now supported. (Hans)
  * Fixed broken ForeachTask task.  (Manuel)

Dec 24, 2003 - Phing 2.0.0b1
----------------------------

  * Added PEAR installation framework & ability to build Phing into PEAR package.
  * Added TarTask using PEAR Archive_Tar
  * Added PearPackageTask which creates a PEAR package.xml (using PEAR_PackageFileManager).
  * Added ResolvePathTask which converts relative paths into absolute paths.
  * Removed System class, due to namespace collision w/ PEAR.
  * Basic "working?" tests performed with all selectors.
  * Added selectors:  TypeSelector, ContainsRegexpSelector
  * CreoleSQLExec task is now operational.
  * Corrected non-fatal bugs in: DeleteTask, ReflexiveTask
  * All core Phing classes now in PHP5 syntax (no "var" used, etc.)
  * CopyTask will not stop build execution if a file cannot be copied (will log and continue to next file).
  * New abstract MatchingTask task makes it easier to create your own tasks that use selectors.
  * Removed redundant calls in DirectoryScanner (<fileset> scanning now much faster).
  * Fixed fatal errors in File::equals()

Nov 24, 2003 - Phing 2.0.0a2
----------------------------

  * Fixed ReplaceTokens filter to correctly replace matched tokens
  * Changed "project.basedir" property to be absolute path of basedir
  * Made IntrospectionHelper more tollerant of add*() and addConfigured*() signatures
  * New CvsTask and CvsPassTask for working with CVS repositories
  * New TranslateGettext filter substitutes _("hello!") with "hola!" / "bonjour!" / etc.
  * More consistent use of classhints to enable auto-casting by IntrospectionHelper
  * Fixed infinite loop bug in FileUtils::normalize() for paths containing "/./"
  * Fixed bug in CopyFile/fileset that caused termination of copy operation on encounter of unreadable file

Nov 6, 20003 - Phing 2.0.0a1
----------------------------

  * First release of Phing 2, an extensive rewrite and upgrade.
  * Refactored much of codebase, using new PHP5 features (e.g. Interfaces, Exceptions!)
  * Many, many, many bugfixes to existing functionality
  * Restructuring for more intuitive directory layout, change the parser class names.
  * Introduction of new tasks: AppendTask, ReflexiveTask, ExitTask, Input, PropertyPrompt
  * Introduction of new types: Path, FileList, DirSet, selectors, conditions
  * Introduction of new filters: ReplaceRegexp
  * Introduction of new logger: AnsiColorLogger
  * Many features from ANT 1.5 added to existing Tasks/Types
  * New "Register Slot" functionality allows for tracking "inner" dynamic variables.
