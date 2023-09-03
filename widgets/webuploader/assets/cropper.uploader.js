// ---------------------------------
// ---------  Uploader -------------
// ---------------------------------
var Uploader = (function() {

    var FRAME_WIDTH = 1600;


    var _ = WebUploader;
    var Uploader = _.Uploader;
    var uploaderContainer = $('.uploader-container');
    var uploader, file;

    if ( !Uploader.support() ) {
        alert( 'Your browser is not supported!');
        throw new Error( 'WebUploader does not support the browser you are using.' );
    }

    Uploader.register({
        'before-send-file': 'cropImage'
    }, {

        cropImage: function( file ) {

            var data = file._cropData,
                image, deferred;

            file = this.request( 'get-file', file );
            deferred = _.Deferred();

            image = new _.Lib.Image();

            deferred.always(function() {
                image.destroy();
                image = null;
            });
            image.once( 'error', deferred.reject );
            image.once( 'load', function() {
                image.crop( data.x, data.y, data.width, data.height, data.scale );
            });

            image.once( 'complete', function() {
                var blob, size;

                try {
                    blob = image.getAsBlob();
                    size = file.size;
                    file.source = blob;
                    file.size = blob.size;

                    file.trigger( 'resize', blob.size, size );

                    deferred.resolve();
                } catch ( e ) {
                    console.log( e );
                    deferred.resolve();
                }
            });

            file._info && image.info( file._info );
            file._meta && image.meta( file._meta );
            image.loadFromBlob( file.source );
            return deferred.promise();
        }
    });

    return {
        init: function( selectCb ) {
            uploader = new Uploader({
                pick: {
                    id: '#filePicker',
                    multiple: false
                },

                thumb: {
                    quality: 70,

                    allowMagnify: false,

                    crop: false
                },

                chunked: false,

                compress: false,

                server: '#',
                swf: 'dist/Uploader.swf',
                fileNumLimit: 1,
                onError: function() {
                    var args = [].slice.call(arguments, 0);
                    alert(args.join('\n'));
                }
            });

            uploader.on('fileQueued', function( _file ) {
               
            }).on('uploadSuccess', function() {
                alert('Uploaded successfully');
            });
        },

        crop: function( data ) {

            var scale = Croper.getImageSize().width / file._info.width;
            data.scale = scale;

            file._cropData = {
                x: data.x1,
                y: data.y1,
                width: data.width,
                height: data.height,
                scale: data.scale
            };
        },

        upload: function() {
            uploader.upload();
        },
    }
})();

// ---------------------------------
// ---------  Crpper ---------------
// ---------------------------------
var Croper = (function() {
    var container = $('.cropper-wraper');
    var $image = container.find('.img-container img');
    var btn = $('.upload-btn');
    var isBase64Supported, callback;

    $image.cropper({
        aspectRatio: 1 / 1,
        preview: ".img-preview",
        done: function(data) {
            // console.log(data);
        }
    });

    function srcWrap( src, cb ) {

        // we need to check this at the first time.
        if (typeof isBase64Supported === 'undefined') {
            (function() {
                var data = new Image();
                var support = true;
                data.onload = data.onerror = function() {
                    if( this.width != 1 || this.height != 1 ) {
                        support = false;
                    }
                }
                data.src = src;
                isBase64Supported = support;
            })();
        }

        if ( isBase64Supported ) {
            cb( src );
        } else {
            // otherwise we need server support.
            // convert base64 to a file.
            $.ajax('preview.php', {
                method: 'POST',
                data: src,
                dataType:'json'
            }).done(function( response ) {
                if (response.result) {
                    cb( response.result );
                } else {
                    alert("Preview error");
                }
            });
        }
    }

    btn.on('click', function() {
        callback && callback($image.cropper("getData"));
        return false;
    });

    return {
        setSource: function( src ) {

            srcWrap( src, function( src ) {
                $image.cropper("setImgSrc", src);
            });

            container.removeClass('webuploader-element-invisible');

            return this;
        },

        getImageSize: function() {
            var img = $image.get(0);
            return {
                width: img.naturalWidth,
                height: img.naturalHeight
            }
        },

        setCallback: function( cb ) {
            callback = cb;
            return this;
        },

        disable: function() {
            $image.cropper("disable");
            return this;
        },

        enable: function() {
            $image.cropper("enable");
            return this;
        }
    }

})();
