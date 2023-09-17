<?php

require_once 'SpecContext.php';

require_once 'subcontext/FileContext.php';

class ExampleGroupsContext extends SpecContext
{
    public function __construct(array $params)
    {
        $this->useContext('file', new FileContext());
    }
    
    /** @AfterScenario */
    public static function cleanUp()
    {
        FileContext::deleteTemporaryDir();
    }
    



}