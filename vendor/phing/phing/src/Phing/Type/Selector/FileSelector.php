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

namespace Phing\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Io\File;

/**
 * This is the interface to be used by all selectors.
 *
 * @author  Hans Lellelid, hans@xmpl.org (Phing)
 * @author  Bruce Atherton, bruce@callenish.com (Ant)
 */
interface FileSelector
{
    /**
     * Method that each selector will implement to create their
     * selection behaviour. If there is a problem with the setup
     * of a selector, it can throw a BuildException to indicate
     * the problem.
     *
     * @param File   $basedir  A PhingFile object for the base directory
     * @param string $filename The name of the file to check
     * @param File   $file     A PhingFile object for this filename
     *
     * @throws BuildException if the selector was not configured correctly
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(File $basedir, $filename, File $file);
}
