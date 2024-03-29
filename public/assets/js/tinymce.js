$(document).ready(function() {

    tinymce.init({
        selector: 'textarea.tinymce',
        height: 500,
        theme: 'silver',
        plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help',
        toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
        images_upload_url: '/dashboard/media/upload',
        relative_urls : false,
        remove_script_host : false,
        convert_urls : true,
        image_dimensions: false,
        image_class_list: [
            {title: 'Responsive', value: 'img-responsive'}
        ],
        language: 'de',images_upload_handler: function (blobInfo, success, failure) {
            let xhr, formData;

            let url = '/dashboard/media/upload/';

            if($('#postType').data('type') === 'page') {
                url = '/dashboard/admin/media/upload/';
            }

            xhr = new XMLHttpRequest();
            xhr.open('POST', url);

            xhr.onload = function() {
                var json;

                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                success(json.location);
            };

            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('linkedPost', $('#postID').data('id'));
            formData.append('type', $('#postType').data('type'));

            xhr.send(formData);
        }
    });

});