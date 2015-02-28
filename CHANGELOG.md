ChangeLog
=========

0.3.1 (2015-02-08)
------------------

* Added `XmlDeserializable` to match `XmlSerializable`.


0.3.0 (2015-02-06)
------------------

* Added `$elementMap` argument to parseInnerTree, for quickly overriding
  parsing rules within an element.


0.2.2 (2015-02-05)
------------------

* Now depends on sabre/uri 1.0.


0.2.1 (2014-12-17)
------------------

* LibXMLException now inherits from ParseException, so it's easy for users to
  catch any exception thrown by the parser.


0.2.0 (2014-12-05)
------------------

* Major BC Break: method names for the Element interface have been renamed
  from `serializeXml` and `deserializeXml` to `xmlSerialize` and
  `xmlDeserialize`. This is so that it matches PHP's `JsonSerializable`
  interface.
* #25: Added `XmlSerializable` to allow people to write serializers without
  having to implement a deserializer in the same class.
* #26: Renamed the `Sabre\XML` namespace to `Sabre\Xml`. Due to composer magic
  and the fact that PHP namespace are case-insensitive, this should not affect
  anyone, unless you are doing exact string matches on class names.
* #23: It's not possible to automatically extract or serialize Xml fragments
  from documents using `Sabre\Xml\Element\XmlFragment`.


0.1.0 (2014-11-24)
------------------

* #16: Added ability to override `elementMap`, `namespaceMap` and `baseUri` for
  a fragment of a document during reading an writing using `pushContext` and
  `popContext`.
* Removed: `Writer::$context` and `Reader::$context`.
* #15: Added `Reader::$baseUri` to match `Writer::$baseUri`.
* #20: Allow callbacks to be used instead of `Element` classes in the `Reader`.
* #25: Added `readText` to quickly grab all text from a node and advance the
  reader to the next node.
* #15: Added `Sabre\XML\Element\Uri`.


0.0.6 (2014-09-26)
------------------

* Added: `CData` element.
* #13: Better support for xml with no namespaces. (@kalmas)
* Switched to PSR-4 directory structure.


0.0.5 (2013-03-27)
------------------

* Added: baseUri property to the Writer class.
* Added: The writeElement method can now write complex elements.
* Added: Throwing exception when invalid objects are written.


0.0.4 (2013-03-14)
------------------

* Fixed: The KeyValue parser was skipping over elements when there was no
  whitespace between them.
* Fixed: Clearing libxml errors after parsing.
* Added: Support for CDATA.
* Added: Context properties.


0.0.3 (2013-02-22)
------------------

* Changed: Reader::parse returns an array with 1 level less depth.
* Added: A LibXMLException is now thrown if the XMLReader comes across an error.
* Fixed: Both the Elements and KeyValue parsers had severe issues with
  nesting.
* Fixed: The reader now detects when the end of the document is hit before it
  should (because we're still parsing an element).


0.0.2 (2013-02-17)
------------------

* Added: Elements parser.
* Added: KeyValue parser.
* Change: Reader::parseSubTree is now named parseInnerTree, and returns either
  a string (in case of a text-node), or an array (in case there were child
  elements).
* Added: Reader::parseCurrentElement is now public.


0.0.1 (2013-02-07)
------------------

* First alpha release

Project started: 2012-11-13. First experiments in June 2009.
