PHPWordFinder
==============

Backend word finder and word definition service  

See the full project description [here](https://github.com/cheshirec7/wordfinder).

Built with the [Lumen](https://lumen.laravel.com/) PHP micro-framework

Find words given letters:  
http://phpwordfinder.erictotten.net/find?tray=test&rt=[html,json]&wc=[0+]  

Where:  
* tray (required): 2+ letters
* wc (opt): a number indicating number of wild card characters
* rt (opt): return type, 'html' (default) or 'json'


Define a word:  
http://phpwordfinder.erictotten.net/define/xxxx, where xxxx is the word to define