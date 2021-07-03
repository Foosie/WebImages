# WebImages

Displays images and videos in a web page.  Creates thumbnails. One php file.  Some mobile support.

--

Just drop this file into a folder on your web server containing images and videos.  It will automagically create thumbnails for you and render a web page.  Viewing Next and Previous images is fully supported.

The first time it is run could take considerable time while creating thumbnails. Nothing will be displayed while this is happening. It will just churn. If you have considerable images, php could time out and you may need to try again resuming the thumbnail creation.

Also, many files can cause a bit of a lag while building the image array.  This is especially true if $recursive=TRUE in the php file.

The web page is password protected.  Change to suit.

favicon.ico is also supported.
