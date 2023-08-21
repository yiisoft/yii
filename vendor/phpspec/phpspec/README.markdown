PHPSpec
=======

[![Build Status](https://secure.travis-ci.org/phpspec/phpspec.png)](http://travis-ci.org/phpspec/phpspec])

**PHPSpec** is a A Behaviour-Driven Development Framework for PHP.

What is PHPSpec
---------------

PHPSpec is a framework which provides programmers with a Domain
Specific Language to describe the behaviour of PHP code with readable,
executable examples that guide you in the design process and serve well as
both documentation and tests.

Getting started
---------------

PHPSpec is a simple framework. As the official description suggests
you can write code examples which are repeatable. This means you write an
example, and may repeat it as often as you wish to ensure the
implementation code it relates to continues to abide by the example.
PHPSpec is related to Unit Testing, but our Behaviour-Driven Development
(BDD) origins determine we use a clear plain English style API. This API,
given it's fluent style and approximation of natural language is therefore
referred to as a Domain Specific Language (DSL) domain specific language.

At a deeper level, PHPSpec was designed entirely with BDD in mind. Though
similar to Unit Testing, the framework is being designed to support BDD from the
ground so that learning, understanding and practicing BDD is as easy as
possible.

You Start By Writing A Specification
------------------------------------

In practicing Behaviour-Driven Development, everything starts with a
specification.

A specification is a collection of examples specific to a particular
context. If you image a game of 10-pin Bowling, we can assume that the
game has a beginning. So our first context, is a game prior to it
starting. Each example should describe some facet of behaviour. A Bowling
game, for example, is subject to rules concerning scores. So writing an
example showing how many pins hit relates to the resulting score would
capture such behaviour quite well.

Start by writing a simple example that expresses a small part of the
behaviour our new Bowling game should exhibit.

    <?php
    class DescribeNewBowlingGame extends \PHPSpec\Context
    {
    
        private $_bowling;
    
        public function before()
        {
            $this->_bowling = new Bowling;
        }
    
        public function itShouldScore0ForGutterGame()
        {
            for ($i=1; $i<=20; $i++) {
                $this->_bowling->hit(0); // someone is really bad at bowling!
            }
            $this->spec($this->_bowling->score)->should->equal(0);
        }
    
    }

You can execute this example by saving the class to NewBowlingGameSpec.php,
navigating to its location from the command line and running the phpspec
command as follows.

    phpspec NewBowlingGameSpec.php
    
Run the example and it will fail since no Bowling class really exists.

Naming conventions also allow for the DescribeNewBowlingGame class to be
defined in a file called DescribeNewBowlingGame.php, although the first is
common in other BDD frameworks and is replicated here for easier transition
from other BDD frameworks to PHPSpec. On the command line the phpspec
parameter is always the filename or the filename with the ".php" file
suffix removed. Whichever convention you prefer - be sure to apply it
consistently.

Now write just enough code to make it pass
------------------------------------------

    <?php
    class Bowling
    {
    
        public $score = 0;
    
        public function hit()
        {     
        }
    
    }

Rerun the example and enjoy the result of a passing spec.

As we note below, take very small steps. We've implemented our first
example so to write more code, we should first write more examples
demonstrating how any new code should behave. This is encouraged by all
spec methods, which must start with the term "itShould". Perhaps the next
step is hitting one or more pins - that would allow us to predict a score
for an example, and also lead us to implementing some scoring rules for
our new Bowling::hit() method.

