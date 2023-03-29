/**
 * Faking the Drupal filter '|without' because TwigJS doesn't know it
 */

'use strict';


const withoutTwigFilter = function (Twig) {

  Twig.extendFilter("without", function(value, args) {

    if (typeof value !== 'undefined') {
      var newValue = value;

      // see if our value contains a key that is the first argument
      if (typeof newValue[args[0]] !== 'undefined') {
        var term = newValue[args[0]];

        // if our value is an array, look for objects with this term
        if (Array.isArray(newValue) ) {

          // check all items to see if they are objects with a key/value pair
          // and see if the key matches the term

          // loop the array, eg [{fu: bar, a: b, c: d}]
          for (var i in newValue) {
            if (newValue.hasOwnProperty(i)) {

              var item = newValue[key]; // eg. {fu: bar}

              // check if item is an object and has the term as key

              if (typeof item === 'object' && item !== null && typeof item[term] !== 'undefined') {
                // remove the item from our array
                newValue.splice(i, 1);
              }

            }
          }
        }

        // if it's an object, we can look for our term and remove it straightaway
        if (typeof newValue === 'object' && newValue !== null && typeof newValue[term] !== 'undefined') {
          // remove the item from our object
          delete newValue[term];
        }

        // Most of the time though, the value is a string, eg. printing all the attributes: 'class="test" title="my label"'
        // so we should cut the term out of it, eg. 'class="test" /title="my label"/' => 'class="test"'
        //
        // TO DO
      }

    }

    return newValue;
  });

}

export default withoutTwigFilter;
