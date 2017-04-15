
<?php
/**
 * Auto-generated class. AVRC syntax highlighting
 * 
 * 
 * 		C/C++ highlighter specific to Atmel AVR microcontrollers
 *      
 *
 * PHP version 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @link       http://pear.php.net/package/Text_Highlighter
 * @category   Text
 * @package    Text_Highlighter
 * @version    generated from: avrc.xml
 * @author Andrey Demenev <demenev@gmail.com>
 *
 */


/**
 * Auto-generated class. AVRC syntax highlighting
 *
 * @author Andrey Demenev <demenev@gmail.com>
 * @category   Text
 * @package    Text_Highlighter
 * @copyright  2004-2006 Andrey Demenev
 * @license    http://www.php.net/license/3_0.txt  PHP License
 * @version    Release: 0.7.0
 * @link       http://pear.php.net/package/Text_Highlighter
 */
class  Text_Highlighter_AVRC extends Text_Highlighter
{
    var $_language = 'avrc';

    /**
     *  Constructor
     *
     * @param array  $options
     * @access public
     */
    function __construct($options=array())
    {

        $this->_options = $options;
        $this->_regs = array (
            -1 => '/((?i)")|((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*)|((?i)\\b0[xX][\\da-f]+)|((?i)\\b\\d\\d*|\\b0\\b)|((?i)\\b0[0-7]+)|((?i)\\b(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?mi)^[ \\t]*#include)|((?mii)^[ \\t]*#[ \\t]*[a-z]+)|((?i)\\d*\\.?\\d+)|((?i)\\/\\*)|((?i)\\/\\/.+)/',
            0 => '/((?i)\\\\)/',
            1 => '/((?i)")|((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*)|((?i)\\b0[xX][\\da-f]+)|((?i)\\b\\d\\d*|\\b0\\b)|((?i)\\b0[0-7]+)|((?i)\\b(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?mi)^[ \\t]*#include)|((?mii)^[ \\t]*#[ \\t]*[a-z]+)|((?i)\\d*\\.?\\d+)|((?i)\\/\\*)|((?i)\\/\\/.+)/',
            2 => '/((?i)")|((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*)|((?i)\\b0[xX][\\da-f]+)|((?i)\\b\\d\\d*|\\b0\\b)|((?i)\\b0[0-7]+)|((?i)\\b(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?mi)^[ \\t]*#include)|((?mii)^[ \\t]*#[ \\t]*[a-z]+)|((?i)\\d*\\.?\\d+)|((?i)\\/\\*)|((?i)\\/\\/.+)/',
            3 => '/((?i)")|((?i)\\{)|((?i)\\()|((?i)\\[)|((?i)[a-z_]\\w*)|((?i)\\b0[xX][\\da-f]+)|((?i)\\b\\d\\d*|\\b0\\b)|((?i)\\b0[0-7]+)|((?i)\\b(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?mi)^[ \\t]*#include)|((?mii)^[ \\t]*#[ \\t]*[a-z]+)|((?i)\\d*\\.?\\d+)|((?i)\\/\\*)|((?i)\\/\\/.+)/',
            4 => '//',
            5 => '/((?i)")|((?i)<)/',
            6 => '/((?i)")|((?i)\\{)|((?i)\\()|((?i)[a-z_]\\w*)|((?i)\\b0[xX][\\da-f]+)|((?i)\\b\\d\\d*|\\b0\\b)|((?i)\\b0[0-7]+)|((?i)\\b(\\d*\\.\\d+)|(\\d+\\.\\d*))|((?i)\\/\\*)|((?i)\\/\\/.+)/',
            7 => '/((?i)\\$\\w+\\s*:.+\\$)/',
            8 => '/((?i)\\$\\w+\\s*:.+\\$)/',
        );
        $this->_counts = array (
            -1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
                10 => 0,
                11 => 0,
                12 => 0,
                13 => 0,
            ),
            0 => 
            array (
                0 => 0,
            ),
            1 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
                10 => 0,
                11 => 0,
                12 => 0,
                13 => 0,
            ),
            2 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
                10 => 0,
                11 => 0,
                12 => 0,
                13 => 0,
            ),
            3 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 0,
                8 => 2,
                9 => 0,
                10 => 0,
                11 => 0,
                12 => 0,
                13 => 0,
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => 0,
                1 => 0,
            ),
            6 => 
            array (
                0 => 0,
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0,
                7 => 2,
                8 => 0,
                9 => 0,
            ),
            7 => 
            array (
                0 => 0,
            ),
            8 => 
            array (
                0 => 0,
            ),
        );
        $this->_delim = array (
            -1 => 
            array (
                0 => 'quotes',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'brackets',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => 'prepro',
                10 => 'prepro',
                11 => '',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            0 => 
            array (
                0 => '',
            ),
            1 => 
            array (
                0 => 'quotes',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'brackets',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => 'prepro',
                10 => 'prepro',
                11 => '',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            2 => 
            array (
                0 => 'quotes',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'brackets',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => 'prepro',
                10 => 'prepro',
                11 => '',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            3 => 
            array (
                0 => 'quotes',
                1 => 'brackets',
                2 => 'brackets',
                3 => 'brackets',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => '',
                9 => 'prepro',
                10 => 'prepro',
                11 => '',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => 'quotes',
                1 => 'quotes',
            ),
            6 => 
            array (
                0 => 'quotes',
                1 => 'brackets',
                2 => 'brackets',
                3 => '',
                4 => '',
                5 => '',
                6 => '',
                7 => '',
                8 => 'mlcomment',
                9 => 'comment',
            ),
            7 => 
            array (
                0 => '',
            ),
            8 => 
            array (
                0 => '',
            ),
        );
        $this->_inner = array (
            -1 => 
            array (
                0 => 'string',
                1 => 'code',
                2 => 'code',
                3 => 'code',
                4 => 'identifier',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'prepro',
                10 => 'code',
                11 => 'number',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            0 => 
            array (
                0 => 'special',
            ),
            1 => 
            array (
                0 => 'string',
                1 => 'code',
                2 => 'code',
                3 => 'code',
                4 => 'identifier',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'prepro',
                10 => 'code',
                11 => 'number',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            2 => 
            array (
                0 => 'string',
                1 => 'code',
                2 => 'code',
                3 => 'code',
                4 => 'identifier',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'prepro',
                10 => 'code',
                11 => 'number',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            3 => 
            array (
                0 => 'string',
                1 => 'code',
                2 => 'code',
                3 => 'code',
                4 => 'identifier',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'number',
                9 => 'prepro',
                10 => 'code',
                11 => 'number',
                12 => 'mlcomment',
                13 => 'comment',
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => 'string',
                1 => 'string',
            ),
            6 => 
            array (
                0 => 'string',
                1 => 'code',
                2 => 'code',
                3 => 'identifier',
                4 => 'number',
                5 => 'number',
                6 => 'number',
                7 => 'number',
                8 => 'mlcomment',
                9 => 'comment',
            ),
            7 => 
            array (
                0 => 'inlinedoc',
            ),
            8 => 
            array (
                0 => 'inlinedoc',
            ),
        );
        $this->_end = array (
            0 => '/(?i)"/',
            1 => '/(?i)\\}/',
            2 => '/(?i)\\)/',
            3 => '/(?i)\\]/',
            4 => '/(?i)>/',
            5 => '/(?mi)(?<!\\\\)$/',
            6 => '/(?mi)(?<!\\\\)$/',
            7 => '/(?i)\\*\\//',
            8 => '/(?mi)$/',
        );
        $this->_states = array (
            -1 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => 5,
                10 => 6,
                11 => -1,
                12 => 7,
                13 => 8,
            ),
            0 => 
            array (
                0 => -1,
            ),
            1 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => 5,
                10 => 6,
                11 => -1,
                12 => 7,
                13 => 8,
            ),
            2 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => 5,
                10 => 6,
                11 => -1,
                12 => 7,
                13 => 8,
            ),
            3 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => -1,
                9 => 5,
                10 => 6,
                11 => -1,
                12 => 7,
                13 => 8,
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => 0,
                1 => 4,
            ),
            6 => 
            array (
                0 => 0,
                1 => 1,
                2 => 2,
                3 => -1,
                4 => -1,
                5 => -1,
                6 => -1,
                7 => -1,
                8 => 7,
                9 => 8,
            ),
            7 => 
            array (
                0 => -1,
            ),
            8 => 
            array (
                0 => -1,
            ),
        );
        $this->_keywords = array (
            -1 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 
                array (
                    'reserved' => '/^(and|and_eq|asm|bitand|bitor|break|case|catch|compl|const_cast|continue|default|delete|do|dynamic_cast|else|for|fortran|friend|goto|if|new|not|not_eq|operator|or|or_eq|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|try|typeid|using|while|xor|xor_eq|false|true)$/',
                    'registers' => '/^(ACSR|ADCH|ADCL|ADCSRA|ADMUX|ASSR|DDRA|DDRB|DDRC|DDRD|DDRE|DDRF|DDRG|EEARH|EEARL|EECR|EEDR|EICRA|EICRB|EIFR|EIMSK|ETIFR|ETIMSK|GICR|GIFR|ICR1H|ICR1L|ICR3H|ICR3L|MCUCR|MCUCSR|OCDR|OCR0|OCR1AH|OCR1AL|OCR1BH|OCR1BL|OCR1CH|OCR1CL|OCR2|OCR3AH|OCR3AL|OCR3BH|OCR3BL|OCR3CH|OCR3CL|OSCCAL|PINA|PINB|PINC|PIND|PINE|PINF|PING|PORTA|PORTB|PORTC|PORTD|PORTE|PORTF|PORTG|RAMPZ|SFIOR|SPCR|SPDR|SPH|SPL|SPMCR|SPMCSR|SPSR|SREG|TCCR0|TCCR1A|TCCR1B|TCCR1C|TCCR2|TCCR3A|TCCR3B|TCCR3C|TCNT0|TCNT1H|TCNT1L|TCNT2|TCNT3H|TCNT3L|TIFR|TIMSK|TWAR|TWBR|TWCR|TWDR|TWSR|UBRR0H|UBRR0L|UBRR1H|UBRR1L|UBRRH|UBRRL|UCSR0A|UCSR0B|UCSR0C|UCSR1A|UCSR1B|UCSR1C|UCSRA|UCSRB|UCSRC|UDR|UDR0|UDR1|WDTCR|XDIV|XMCRA|XMCRB)$/',
                    'types' => '/^(auto|bool|char|class|const|double|enum|explicit|export|extern|float|inline|int|long|mutable|namespace|register|short|signed|static|struct|template|typedef|typename|union|unsigned|virtual|void|volatile|wchar_t)$/',
                    'Common Macros' => '/^(NULL|TRUE|FALSE|MAX|MIN|__LINE__|__DATA__|__FILE__|__TIME__|__STDC__)$/',
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => 
                array (
                ),
                9 => -1,
                10 => -1,
                11 => 
                array (
                ),
                12 => -1,
                13 => -1,
            ),
            0 => 
            array (
                0 => 
                array (
                ),
            ),
            1 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 
                array (
                    'reserved' => '/^(and|and_eq|asm|bitand|bitor|break|case|catch|compl|const_cast|continue|default|delete|do|dynamic_cast|else|for|fortran|friend|goto|if|new|not|not_eq|operator|or|or_eq|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|try|typeid|using|while|xor|xor_eq|false|true)$/',
                    'registers' => '/^(ACSR|ADCH|ADCL|ADCSRA|ADMUX|ASSR|DDRA|DDRB|DDRC|DDRD|DDRE|DDRF|DDRG|EEARH|EEARL|EECR|EEDR|EICRA|EICRB|EIFR|EIMSK|ETIFR|ETIMSK|GICR|GIFR|ICR1H|ICR1L|ICR3H|ICR3L|MCUCR|MCUCSR|OCDR|OCR0|OCR1AH|OCR1AL|OCR1BH|OCR1BL|OCR1CH|OCR1CL|OCR2|OCR3AH|OCR3AL|OCR3BH|OCR3BL|OCR3CH|OCR3CL|OSCCAL|PINA|PINB|PINC|PIND|PINE|PINF|PING|PORTA|PORTB|PORTC|PORTD|PORTE|PORTF|PORTG|RAMPZ|SFIOR|SPCR|SPDR|SPH|SPL|SPMCR|SPMCSR|SPSR|SREG|TCCR0|TCCR1A|TCCR1B|TCCR1C|TCCR2|TCCR3A|TCCR3B|TCCR3C|TCNT0|TCNT1H|TCNT1L|TCNT2|TCNT3H|TCNT3L|TIFR|TIMSK|TWAR|TWBR|TWCR|TWDR|TWSR|UBRR0H|UBRR0L|UBRR1H|UBRR1L|UBRRH|UBRRL|UCSR0A|UCSR0B|UCSR0C|UCSR1A|UCSR1B|UCSR1C|UCSRA|UCSRB|UCSRC|UDR|UDR0|UDR1|WDTCR|XDIV|XMCRA|XMCRB)$/',
                    'types' => '/^(auto|bool|char|class|const|double|enum|explicit|export|extern|float|inline|int|long|mutable|namespace|register|short|signed|static|struct|template|typedef|typename|union|unsigned|virtual|void|volatile|wchar_t)$/',
                    'Common Macros' => '/^(NULL|TRUE|FALSE|MAX|MIN|__LINE__|__DATA__|__FILE__|__TIME__|__STDC__)$/',
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => 
                array (
                ),
                9 => -1,
                10 => -1,
                11 => 
                array (
                ),
                12 => -1,
                13 => -1,
            ),
            2 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 
                array (
                    'reserved' => '/^(and|and_eq|asm|bitand|bitor|break|case|catch|compl|const_cast|continue|default|delete|do|dynamic_cast|else|for|fortran|friend|goto|if|new|not|not_eq|operator|or|or_eq|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|try|typeid|using|while|xor|xor_eq|false|true)$/',
                    'registers' => '/^(ACSR|ADCH|ADCL|ADCSRA|ADMUX|ASSR|DDRA|DDRB|DDRC|DDRD|DDRE|DDRF|DDRG|EEARH|EEARL|EECR|EEDR|EICRA|EICRB|EIFR|EIMSK|ETIFR|ETIMSK|GICR|GIFR|ICR1H|ICR1L|ICR3H|ICR3L|MCUCR|MCUCSR|OCDR|OCR0|OCR1AH|OCR1AL|OCR1BH|OCR1BL|OCR1CH|OCR1CL|OCR2|OCR3AH|OCR3AL|OCR3BH|OCR3BL|OCR3CH|OCR3CL|OSCCAL|PINA|PINB|PINC|PIND|PINE|PINF|PING|PORTA|PORTB|PORTC|PORTD|PORTE|PORTF|PORTG|RAMPZ|SFIOR|SPCR|SPDR|SPH|SPL|SPMCR|SPMCSR|SPSR|SREG|TCCR0|TCCR1A|TCCR1B|TCCR1C|TCCR2|TCCR3A|TCCR3B|TCCR3C|TCNT0|TCNT1H|TCNT1L|TCNT2|TCNT3H|TCNT3L|TIFR|TIMSK|TWAR|TWBR|TWCR|TWDR|TWSR|UBRR0H|UBRR0L|UBRR1H|UBRR1L|UBRRH|UBRRL|UCSR0A|UCSR0B|UCSR0C|UCSR1A|UCSR1B|UCSR1C|UCSRA|UCSRB|UCSRC|UDR|UDR0|UDR1|WDTCR|XDIV|XMCRA|XMCRB)$/',
                    'types' => '/^(auto|bool|char|class|const|double|enum|explicit|export|extern|float|inline|int|long|mutable|namespace|register|short|signed|static|struct|template|typedef|typename|union|unsigned|virtual|void|volatile|wchar_t)$/',
                    'Common Macros' => '/^(NULL|TRUE|FALSE|MAX|MIN|__LINE__|__DATA__|__FILE__|__TIME__|__STDC__)$/',
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => 
                array (
                ),
                9 => -1,
                10 => -1,
                11 => 
                array (
                ),
                12 => -1,
                13 => -1,
            ),
            3 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => -1,
                4 => 
                array (
                    'reserved' => '/^(and|and_eq|asm|bitand|bitor|break|case|catch|compl|const_cast|continue|default|delete|do|dynamic_cast|else|for|fortran|friend|goto|if|new|not|not_eq|operator|or|or_eq|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|try|typeid|using|while|xor|xor_eq|false|true)$/',
                    'registers' => '/^(ACSR|ADCH|ADCL|ADCSRA|ADMUX|ASSR|DDRA|DDRB|DDRC|DDRD|DDRE|DDRF|DDRG|EEARH|EEARL|EECR|EEDR|EICRA|EICRB|EIFR|EIMSK|ETIFR|ETIMSK|GICR|GIFR|ICR1H|ICR1L|ICR3H|ICR3L|MCUCR|MCUCSR|OCDR|OCR0|OCR1AH|OCR1AL|OCR1BH|OCR1BL|OCR1CH|OCR1CL|OCR2|OCR3AH|OCR3AL|OCR3BH|OCR3BL|OCR3CH|OCR3CL|OSCCAL|PINA|PINB|PINC|PIND|PINE|PINF|PING|PORTA|PORTB|PORTC|PORTD|PORTE|PORTF|PORTG|RAMPZ|SFIOR|SPCR|SPDR|SPH|SPL|SPMCR|SPMCSR|SPSR|SREG|TCCR0|TCCR1A|TCCR1B|TCCR1C|TCCR2|TCCR3A|TCCR3B|TCCR3C|TCNT0|TCNT1H|TCNT1L|TCNT2|TCNT3H|TCNT3L|TIFR|TIMSK|TWAR|TWBR|TWCR|TWDR|TWSR|UBRR0H|UBRR0L|UBRR1H|UBRR1L|UBRRH|UBRRL|UCSR0A|UCSR0B|UCSR0C|UCSR1A|UCSR1B|UCSR1C|UCSRA|UCSRB|UCSRC|UDR|UDR0|UDR1|WDTCR|XDIV|XMCRA|XMCRB)$/',
                    'types' => '/^(auto|bool|char|class|const|double|enum|explicit|export|extern|float|inline|int|long|mutable|namespace|register|short|signed|static|struct|template|typedef|typename|union|unsigned|virtual|void|volatile|wchar_t)$/',
                    'Common Macros' => '/^(NULL|TRUE|FALSE|MAX|MIN|__LINE__|__DATA__|__FILE__|__TIME__|__STDC__)$/',
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => 
                array (
                ),
                9 => -1,
                10 => -1,
                11 => 
                array (
                ),
                12 => -1,
                13 => -1,
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => -1,
                1 => -1,
            ),
            6 => 
            array (
                0 => -1,
                1 => -1,
                2 => -1,
                3 => 
                array (
                    'reserved' => '/^(and|and_eq|asm|bitand|bitor|break|case|catch|compl|const_cast|continue|default|delete|do|dynamic_cast|else|for|fortran|friend|goto|if|new|not|not_eq|operator|or|or_eq|private|protected|public|reinterpret_cast|return|sizeof|static_cast|switch|this|throw|try|typeid|using|while|xor|xor_eq|false|true)$/',
                    'registers' => '/^(ACSR|ADCH|ADCL|ADCSRA|ADMUX|ASSR|DDRA|DDRB|DDRC|DDRD|DDRE|DDRF|DDRG|EEARH|EEARL|EECR|EEDR|EICRA|EICRB|EIFR|EIMSK|ETIFR|ETIMSK|GICR|GIFR|ICR1H|ICR1L|ICR3H|ICR3L|MCUCR|MCUCSR|OCDR|OCR0|OCR1AH|OCR1AL|OCR1BH|OCR1BL|OCR1CH|OCR1CL|OCR2|OCR3AH|OCR3AL|OCR3BH|OCR3BL|OCR3CH|OCR3CL|OSCCAL|PINA|PINB|PINC|PIND|PINE|PINF|PING|PORTA|PORTB|PORTC|PORTD|PORTE|PORTF|PORTG|RAMPZ|SFIOR|SPCR|SPDR|SPH|SPL|SPMCR|SPMCSR|SPSR|SREG|TCCR0|TCCR1A|TCCR1B|TCCR1C|TCCR2|TCCR3A|TCCR3B|TCCR3C|TCNT0|TCNT1H|TCNT1L|TCNT2|TCNT3H|TCNT3L|TIFR|TIMSK|TWAR|TWBR|TWCR|TWDR|TWSR|UBRR0H|UBRR0L|UBRR1H|UBRR1L|UBRRH|UBRRL|UCSR0A|UCSR0B|UCSR0C|UCSR1A|UCSR1B|UCSR1C|UCSRA|UCSRB|UCSRC|UDR|UDR0|UDR1|WDTCR|XDIV|XMCRA|XMCRB)$/',
                    'types' => '/^(auto|bool|char|class|const|double|enum|explicit|export|extern|float|inline|int|long|mutable|namespace|register|short|signed|static|struct|template|typedef|typename|union|unsigned|virtual|void|volatile|wchar_t)$/',
                    'Common Macros' => '/^(NULL|TRUE|FALSE|MAX|MIN|__LINE__|__DATA__|__FILE__|__TIME__|__STDC__)$/',
                ),
                4 => 
                array (
                ),
                5 => 
                array (
                ),
                6 => 
                array (
                ),
                7 => 
                array (
                ),
                8 => -1,
                9 => -1,
            ),
            7 => 
            array (
                0 => 
                array (
                ),
            ),
            8 => 
            array (
                0 => 
                array (
                ),
            ),
        );
        $this->_parts = array (
            0 => 
            array (
                0 => NULL,
            ),
            1 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => NULL,
                11 => NULL,
                12 => NULL,
                13 => NULL,
            ),
            2 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => NULL,
                11 => NULL,
                12 => NULL,
                13 => NULL,
            ),
            3 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
                10 => NULL,
                11 => NULL,
                12 => NULL,
                13 => NULL,
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => NULL,
                1 => NULL,
            ),
            6 => 
            array (
                0 => NULL,
                1 => NULL,
                2 => NULL,
                3 => NULL,
                4 => NULL,
                5 => NULL,
                6 => NULL,
                7 => NULL,
                8 => NULL,
                9 => NULL,
            ),
            7 => 
            array (
                0 => NULL,
            ),
            8 => 
            array (
                0 => NULL,
            ),
        );
        $this->_subst = array (
            -1 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
            ),
            0 => 
            array (
                0 => false,
            ),
            1 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
            ),
            2 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
            ),
            3 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
                9 => false,
                10 => false,
                11 => false,
                12 => false,
                13 => false,
            ),
            4 => 
            array (
            ),
            5 => 
            array (
                0 => false,
                1 => false,
            ),
            6 => 
            array (
                0 => false,
                1 => false,
                2 => false,
                3 => false,
                4 => false,
                5 => false,
                6 => false,
                7 => false,
                8 => false,
                9 => false,
            ),
            7 => 
            array (
                0 => false,
            ),
            8 => 
            array (
                0 => false,
            ),
        );
        $this->_conditions = array (
        );
        $this->_kwmap = array (
            'reserved' => 'reserved',
            'registers' => 'reserved',
            'types' => 'types',
            'Common Macros' => 'prepro',
        );
        $this->_defClass = 'code';
        $this->_checkDefines();
    }
    
}