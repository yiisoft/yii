<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Util;

/**
 * Static class to handle a slot-listening system.
 *
 * Unlike the slots/signals Qt model, this class manages something that is
 * more like a simple hashtable, where each slot has only one value.  For that
 * reason "Registers" makes more sense, the reference being to CPU registers.
 *
 * This could be used for anything, but it's been built for a pretty specific phing
 * need, and that is to allow access to dynamic values that are set by logic
 * that is not represented in a build file.  For exampe, we need a system for getting
 * the current resource (file) that is being processed by a filterchain in a fileset.
 *
 * Each slot corresponds to only one read-only, dynamic-value RegisterSlot object. In
 * a build.xml register slots are expressed using a syntax similar to variables:
 *
 * <replaceregexp>
 *    <regexp pattern="\n" replace="%{task.current_file}"/>
 * </replaceregexp>
 *
 * The task/type must provide a supporting setter for the attribute:
 *
 * <code>
 *     function setListeningReplace(RegisterSlot $slot) {
 *        $this->replace = $slot;
 *  }
 *
 *  // in main()
 *  if ($this->replace instanceof RegisterSlot) {
 *        $this->regexp->setReplace($this->replace->getValue());
 *  } else {
 *        $this->regexp->setReplace($this->replace);
 *  }
 * </code>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class Register
{
    /**
     * Slots that have been registered.
     */
    private static $slots = [];

    /**
     * Returns RegisterSlot for specified key.
     *
     * If not slot exists a new one is created for key.
     *
     * @param string $key
     *
     * @return RegisterSlot
     */
    public static function getSlot($key)
    {
        if (!isset(self::$slots[$key])) {
            self::$slots[$key] = new RegisterSlot($key);
        }

        return self::$slots[$key];
    }
}
