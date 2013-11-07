# CakePHP obfuscation

A CakePHP 2.x ModelBehavior used to transparently obfuscate field values in Models. This can be used to obfuscate any field that contains hex values.

Written and tested on CakePHP 2.3

## Goal

To obfuscate the Model's id to make counting records harder. Also to make it harder for users to modify URLs and guess at other records in the system.

## Usage

Add this plugin to your CakePHP project and use it via the `actsAs` attribute of your Model, like so:

`public $actsAs = array('Obfuscate.Obfuscate');`

Available configuration is as follows:

<table>
  <tr>
    <td>
      fields
    </td>
    <td>
      What fields to obfuscate. By default, it's just `id`, so if you have changed the `primaryKey` value or would like to obfuscate related model records, you'll need to pass them in. See the example below.
    </td>
  </tr><tr>
    <td>
      salt
    </td>
    <td>
      The salt to use when hashing values. Uses `Security.salt` by default.
    </td>
  </tr><tr>
    <td>
      min_length
    </td>
    <td>
      Minimum length of the hash. Set to 6 by default.
    </td>
  </tr><tr>
    <td>
      alphabet
    </td>
    <td>
      The characters to use when generating the hash. Must contain at least 16 unique characters.
    </td>
  </tr>
</table>

Here's an example of how to use this Behavior to obfuscate the User.id field in a related table:

````
// in User model
public $actsAs = array(
    'Obfuscate.Obfuscate' => array(
      'fields' => array(
        'id',
        'Comment.user_id',
      )
    )
  );
````

Remember to make the obfuscation work both ways by using the Behavior in related Models, like so...

````
// in Comment model
public $actsAs = array(
    'Obfuscate.Obfuscate' => array(
      'fields' => array(
        'user_id',
        'User.id'
      )
    )
  );
````

## Thanks

- Ivan Akimov for creating the [hashids](http://www.hashids.org/php/) library which is used for the obfuscation
- Matt Unger of [Roompact](http://roompact.com) for allowing this to be released as Open Source

## License

(MIT License)

Copyright (c) 2013 GoAnsible

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.