Feature: Developer sees version
  As a developer
  In order to know which version of PHPSpec is being run
  I want a command line option that allows just that
  
  Scenario: Long option
  When I run "phpspec --version"
  Then the output should contain:
  """
  1.3.0beta
  
  """