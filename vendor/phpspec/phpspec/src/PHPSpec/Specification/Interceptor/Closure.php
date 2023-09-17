<?php
/**
 * PHPSpec
 *
 * LICENSE
 *
 * This file is subject to the GNU Lesser General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/lgpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpspec.net so we can send you a copy immediately.
 *
 * @category  PHPSpec
 * @package   PHPSpec
 * @copyright Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                    Marcello Duarte
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
namespace PHPSpec\Specification\Interceptor;

use \PHPSpec\Specification\Interceptor;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Closure extends Interceptor
{
    /**
     * The closure
     * 
     * @var Closure
     */
    protected $_closure = null;
    
    /**
     * The closure
     * 
     * @var Closure
     */
    protected $_closureException = null;

    /**
     * Scalar is constructed with its value
     * 
     * @param $scalarValue
     */
    public function __construct(\Closure $closure = null)
    {
        if (!is_null($closure)) {
            $this->_closure = $closure;
            try {
                $result = $closure();
                $this->setActualValue($result);
            } catch (\PHPSpec\Exception $e) {
                if (!\PHPSpec\PHPSpec::testingPHPSpec()) {
                    throw $e;
                }
                $this->_composedActual = true;
                $this->setActualValue(array(get_class($e), $e->getMessage()));
            } catch(\Exception $e) {
                $this->_composedActual = true;
                $this->setActualValue(array(get_class($e), $e->getMessage()));
            }
        }
    }
}