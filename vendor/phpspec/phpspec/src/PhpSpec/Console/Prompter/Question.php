<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\Console\Prompter;

use PhpSpec\Console\Prompter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

final class Question implements Prompter
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var QuestionHelper
     */
    private $helper;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
    }

    
    public function askConfirmation(string $question, bool $default = true): bool
    {
        return (bool)$this->helper->ask($this->input, $this->output, new ConfirmationQuestion($question, $default));
    }
}
