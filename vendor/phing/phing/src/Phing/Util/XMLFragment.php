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

use DOMDocument;
use DOMDocumentFragment;
use Phing\Parser\CustomChildCreator;
use Phing\Project;
use Phing\ProjectComponent;

/**
 * Use this class as a nested element if you want to get a literal DOM
 * fragment of something nested into your task/type.
 *
 * <p>This is useful for tasks that want to deal with the "real" XML
 * from the build file instead of objects.</p>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class XMLFragment extends ProjectComponent implements CustomChildCreator
{
    /**
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMDocumentFragment
     */
    private $fragment;

    public function __construct()
    {
        parent::__construct();
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->fragment = $this->doc->createDocumentFragment();
    }

    /**
     * {@inheritDoc}
     */
    public function customChildCreator($elementName, Project $project)
    {
        $e = $this->doc->createElement($elementName);
        $this->fragment->appendChild($e);

        return new XMLChild($project, $this->doc, $this->fragment, $e);
    }

    /**
     * Add nested text, expanding properties as we go.
     *
     * @param string $s the text to add
     */
    public function addText(string $s)
    {
        $s = $this->getProject()->replaceProperties($s);
        //only text nodes that are non null after property expansion are added
        if (null !== $s && '' !== trim($s)) {
            $t = $this->doc->createTextNode(trim($s));
            $this->fragment->appendChild($t);
        }
    }

    public function getFragment(): DOMDocumentFragment
    {
        return $this->fragment;
    }

    public function getDoc(): DOMDocument
    {
        return $this->doc;
    }
}
