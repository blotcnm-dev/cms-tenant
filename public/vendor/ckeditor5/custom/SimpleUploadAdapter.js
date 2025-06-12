
export function SimpleUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new SimpleUploadAdapter(loader);
    };
}



class SimpleUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then(file => {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('file', file);

                //fetch(g5_url + '/plugin/editor/ckeditor5/custom/upload.php',
                console.log(formData);
                fetch('/plugin/editor/ckeditor5/custom/upload.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {

                        // console.log("************************");
                        // console.log("/www/plugin/editor/ckeditor5/custom/SimpleUploadAdapter.js");
                        // console.log(data);


                        if (data.error) {
                            reject(data.error.message);
                        } else {
                            console.log("데이터url==>["+data.url+"]");
                            resolve({
                                default: data.url
                            });
                        }
                    })
                    .catch(error => {
                        console.log("파일 업로드 실패");
                        reject(`파일 업로드 실패: ${file.name}`);
                    });
            });
        });
    }

    abort() {
        // 업로드 중단이 필요한 경우 구현
    }
}
