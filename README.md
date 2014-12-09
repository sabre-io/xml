sabre/xml
=========

[![Build Status](https://secure.travis-ci.org/fruux/sabre-xml.png?branch=master)](http://travis-ci.org/fruux/sabre-xml)

The sabre/xml library is a specialized XML reader and writer.

I often found myself repeating the same pattern for XML manipulation over and
over. This library implements that pattern.

At it's heart, the library maps XML elements to PHP value objects.

The following assumptions are made:

* XML namespaces are used everywhere,
* XML is written and read sequentially,
* All XML elements map to PHP classes and scalars,
* Elements generally contain either just text, just sub-elements or nothing all,
* Elements are represented by classes. A class has a `serializeXml` and a
  `deserializeXml` method,
* Namespace prefixes must be completely ignored by an XML reader.

This is not your average XML library. The intention is not to make this super
simple, but rather very powerful for complex XML applications.

Documentation
-------------

* [Introduction](http://sabre.io/xml/).
* [Installation](http://sabre.io/xml/install/).
* [Reading XML](http://sabre.io/xml/reading/).
* [Writing XML](http://sabre.io/xml/writing/).


Support
-------

Head over to the [SabreDAV mailing list](http://groups.google.com/group/sabredav-discuss) for any questions.

Made at fruux
-------------

This library is being developed by [fruux](https://fruux.com/). Drop us a line for commercial services or enterprise support.
