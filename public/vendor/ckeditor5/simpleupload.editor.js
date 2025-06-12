class SimpleUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(file => {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('file', file);

                fetch(g5_url + '/plugin/editor/ckeditor5/imageUpload/upload.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            reject(data.error.message);
                        } else {
                            console.log(data.url);
                            resolve({
                                default: data.url
                            });
                        }
                    })
                    .catch(error => {
                        reject(`파일 업로드 실패: ${file.name}`);
                    });
            });
        });
    }

    abort() {
        // 업로드 중단이 필요한 경우 구현
    }
}

function CKEditorSimpleUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new SimpleUploadAdapter(loader);
    };
}