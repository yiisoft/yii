Feature: Developer sees help
  As a developer
  In order to know which options are available in PHPSpec
  I want a command line help option
  
  Scenario: Long option
  When I run "phpspec --help"
  Then the output should contain:
  """
  Usage: phpspec (FILE|DIRECTORY) + [options]
      
      -b, --backtrace          Enable full backtrace
      -c, --colour, --color    Enable color in the output
      -e, --example STRING     Run examples whose full nested names include STRING
      -f, --formater FORMATTER Choose a formatter
                                [p]rogress (default - dots)
                                [d]ocumentation (group and example names)
                                [h]tml
                                [j]unit
                                custom formatter class name
      --bootstrap FILENAME     Specify a bootstrap file to run before the tests
      -h, --help               You're looking at it
      --fail-fast              Abort the run on first failure.
      --include-matchers PATHS Specify a : separated list of PATHS to matchers 
      --version                Show version
  
  
  """

  Scenario: Short option
  When I run "phpspec -h"
  Then the output should contain:
  """
  Usage: phpspec (FILE|DIRECTORY) + [options]
      
      -b, --backtrace          Enable full backtrace
      -c, --colour, --color    Enable color in the output
      -e, --example STRING     Run examples whose full nested names include STRING
      -f, --formater FORMATTER Choose a formatter
                                [p]rogress (default - dots)
                                [d]ocumentation (group and example names)
                                [h]tml
                                [j]unit
                                custom formatter class name
      --bootstrap FILENAME     Specify a bootstrap file to run before the tests
      -h, --help               You're looking at it
      --fail-fast              Abort the run on first failure.
      --include-matchers PATHS Specify a : separated list of PATHS to matchers 
      --version                Show version
  
  
  """
  
  Scenario: No option
  When I run "phpspec"
  Then the output should contain:
  """
  phpspec: Invalid number of arguments. Type -h for help
  
  """