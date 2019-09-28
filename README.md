[![Version](http://img.shields.io/packagist/v/stefansl/news_podcasts.svg?style=flat-square)](http://packagist.com/packages/stefansl/news_podcasts)  [![GitHub license](https://img.shields.io/badge/license-GPL-blue.svg?style=flat-square)](https://raw.githubusercontent.com/stefansl/news_podcasts/master/LICENSE)

# News Podcast for Contao

Add podcast files to your news and generate iTunes compatible RSS

## Usage
1. Configure a new podcast in "News" -> "Podcast Feeds"
2. In your news article settings check "add podcast" and pick an audio file from your filesystem.
3. In your /share folder you'll find your new RSS file, which can be tested and provided in iTunes.

## Optional
For faster calculation of duration, install mp3info [http://ibiblio.org/mp3info/] on your server and allow shell_exec.
Recommended for larger podcast files.

## Translation
Translations are managed through Transifex. Feel free to add new: [https://www.transifex.com/projects/p/news_podcasts]

## More Information
Please follow the instruction from the official Apple website: [https://www.apple.com/itunes/podcasts/creatorfaq.html]