Feature: Developer can reuse examples from another spec
  As a developer
  I can use a shared example
  So that I don't need to repeat descriptions common to more than one group

  Scenario: shared examples group included in one file
    Given a file named "MyArraySpec.php" with:
        """
        <?php
        
        class Collection {
            protected $items = array();
            public function add() {
                $this->items = array_merge($this->items, func_get_args());
            }
            public function size() {
                return count($this->items);
            }
        }
        
        class MyArray extends Collection {}
        
        use PHPSpec\Specification\SharedExample;
        
        class ACollection extends SharedExample
        {
            protected $object;
            function before()
            {
                $this->object = $this->spec(new Collection);
            } 
            
            function itSaysItHasThreeItems()
            {
                $this->object->add('one', 'two', 'three');
                $this->object->size()->should->equal(3);
            }
        }
        
        use PHPSpec\Context;
        
        class DescribeMyArray extends Context
        {
            public $itBehavesLike = 'ACollection';
            
            protected $object;
            
            function before()
            {
                $this->object = $this->spec(new MyArray);
            }
        }
        """
    When I run "phpspec MyArraySpec.php -f d"
    Then the output should contain:
        """
        MyArray
          behaves like a collection
            says it has three items
        """
        
    