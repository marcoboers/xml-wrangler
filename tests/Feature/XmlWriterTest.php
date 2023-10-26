<?php

declare(strict_types=1);

use Saloon\XmlWrangler\XmlWriter;
use Saloon\XmlWrangler\Data\Element;
use Saloon\XmlWrangler\Data\RootElement;
use Saloon\XmlWrangler\Exceptions\XmlWriterException;

test('the xml writer can write with just a root element', function () {
    $writer = new XmlWriter;

    $xml = $writer->write('root', []);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root/>

XML
    );
});

test('you can set custom encoding and version on the writer', function () {
    $writer = new XmlWriter;

    $writer->setXmlEncoding('ISO-8859-1');
    $writer->setXmlVersion('2.0');

    $xml = $writer->write('root', ['a' => 'b']);

    expect($xml)->toBe(
        <<<XML
<?xml version="2.0" encoding="ISO-8859-1"?>
<root>
  <a>b</a>
</root>

XML
    );
});

test('can customize the xml declaration', function () {
    //
});

test('xml can be minified', function () {
    $writer = new XmlWriter;

    $xml = $writer->write('root', ['a' => 'b'], true);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root><a>b</a></root>

XML
    );
});

test('the xml writer can accept a custom root element', function () {
    $writer = new XmlWriter;

    $xml = $writer->write(
        rootElement: new RootElement('Envelope', ['a' => 'b'], ['attribute' => 'value'], ['url' => 'https://google.com']),
        content: ['c' => 'd'],
    );

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Envelope attribute="value" xmlns:url="https://google.com">
  <a>b</a>
  <c>d</c>
</Envelope>

XML
    );
});

test('you can add additional processing instructions to the xml', function () {
    $writer = new XmlWriter;

    $writer->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="base.xsl"');

    $xml = $writer->write('root', []);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type="text/xsl" href="base.xsl"?>
<root/>

XML
    );
});

test('closures can be used in the xml writer', function () {
    $writer = new XmlWriter;

    $xml = $writer->write('root', [
        'a' => [
            'b' => function () {
                return [1, 2, Element::make(3)->addAttribute('foo', 'bar')];
            },
        ],
    ]);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root>
  <a>
    <b>1</b>
    <b>2</b>
    <b foo="bar">3</b>
  </a>
</root>

XML
    );
});

test('you cannot use numeric keys in the root element', function (array $content) {
    $writer = new XmlWriter;

    $this->expectException(XmlWriterException::class);
    $this->expectExceptionMessage('The top-most level of content must not have numeric keys.');

    $writer->write('root', $content);
})->with([
    fn () => [1, 2, 3],
    fn () => ['a'],
    fn () => ['a' => 'b', 2],
]);

test('you can use an array of values for multiple elements', function () {
    $writer = new XmlWriter;

    $xml = $writer->write('root', [
        'saloon' => [1, 2, 3],
    ]);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root>
  <saloon>1</saloon>
  <saloon>2</saloon>
  <saloon>3</saloon>
</root>

XML
    );
});

test('the root element can have string content', function () {
    $writer = new XmlWriter;

    $xml = $writer->write(RootElement::make('root', 'howdy'), []);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root>howdy</root>

XML
    );
});

test('the root element content can be merged with the main content', function () {
    $writer = new XmlWriter;

    $xml = $writer->write(RootElement::make('root', ['a' => 'b', 'c' => 'd']), [
        'a' => 'merged',
        'e' => 'f',
    ]);

    expect($xml)->toBe(
        <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root>
  <a>merged</a>
  <c>d</c>
  <e>f</e>
</root>

XML
    );
});