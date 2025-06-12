
class CustomImageUpload {
    constructor(editor) {
        this.editor = editor;
    }

    init() {
        const button = document.createElement('button');
        button.type = 'button';
        button.tabIndex = '-1';
        button.className = 'ck ck-button ck-off';
        button.setAttribute('aria-labelledby', 'ck-editor__aria-label_image_upload');
        button.setAttribute('data-cke-tooltip-text', '이미지 업로드');
        button.setAttribute('data-cke-tooltip-position', 's');

        button.innerHTML = `
            <svg class="ck ck-icon ck-reset_all-excluded ck-icon_inherit-color ck-button__icon" viewBox="0 0 20 20">
                <path d="M6.91 10.54c.26-.23.64-.21.88.03l3.36 3.14 2.23-2.06a.64.64 0 0 1 .87 0l2.52 2.97V4.5H3.2v10.12l3.71-4.08zm10.27-7.51c.6 0 1.09.47 1.09 1.05v11.84c0 .59-.49 1.06-1.09 1.06H2.79c-.6 0-1.09-.47-1.09-1.06V4.08c0-.58.49-1.05 1.1-1.05h14.38zm-5.22 5.56a1.96 1.96 0 1 1 3.4-1.96 1.96 1.96 0 0 1-3.4 1.96z"/>
            </svg>
            <span class="ck ck-button__label" id="ck-editor__aria-label_image_upload">이미지 업로드</span>
        `;

        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.multiple = true;
        input.style.display = 'none';

        button.addEventListener('click', () => {
            input.click();
        });



        input.addEventListener('change', async () => {
            const files = Array.from(input.files);
            if (files.length === 0) return;

            try {
                let imageUrls = [];
                for (const file of files) {
                    const formData = new FormData();
                    formData.append('file', file);

                    const response = await fetch('/plugin/editor/ckeditor5/custom/upload.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('업로드 실패');
                    const data = await response.json();
                    imageUrls.push(data.url);
                }

                console.log("---------------------------------\n");
                console.log(JSON.stringify(imageUrls, null, 2));


                // HTML 문자열 생성
                const imageHtml = `
                    <figure class="editor_figure">
                        <div class="image_container">
                            ${imageUrls.map(url => `<img src="${url}">`).join('')}
                        </div>
                        <figcaption>Caption here</figcaption>
                    </figure>
                `;

                /*
                // 현재 에디터 내용 가져오기
                const currentContent = this.editor.getData();
                // 새 내용 추가
                this.editor.setData(currentContent + imageHtml);

                // 또는 다음 방식도 시도해볼 수 있습니다
                */
                const viewFragment = this.editor.data.processor.toView(imageHtml);
                const modelFragment = this.editor.data.toModel(viewFragment);
                this.editor.model.insertContent(modelFragment);

            } catch (error) {
                console.error('이미지 업로드 실패:', error);
            }

            input.value = '';
        });

        this.editor.ui.view.toolbar.element.appendChild(button);

        // input.addEventListener('change', async () => {
        //     const files = Array.from(input.files);
        //     if (files.length === 0) return;
        //
        //     try {
        //         let imagesHtml = '';
        //         for (const file of files) {
        //             const formData = new FormData();
        //             formData.append('file', file);
        //
        //             const response = await fetch(g5_url + '/plugin/editor/ckeditor5/imageUpload/upload.php', {
        //                 method: 'POST',
        //                 body: formData
        //             });
        //
        //             if (!response.ok) throw new Error('업로드 실패');
        //
        //             const data = await response.json();
        //             console.log("업로드 되는 이미지==>["+data.url+"]");
        //             imagesHtml += `<img src="${data.url}">`;
        //         }
        //
        //         if (imagesHtml) {
        //             const customHtml = `
        //                 <figure class="image editor_figure">
        //                     <div class="image_container">
        //                         ${imagesHtml}
        //                     </div>
        //                 </figure>
        //             `;
        //
        //             const currentContent = this.editor.getData();
        //
        //             console.log("현재====================>");
        //             console.log(currentContent);
        //             console.log("더해진 후 ====================>");
        //             console.log(currentContent + customHtml);
        //             this.editor.setData(currentContent + customHtml);
        //         }
        //
        //     } catch (error) {
        //         console.error('이미지 업로드 실패:', error);
        //     }
        //
        //     input.value = '';
        // });
        //this.editor.ui.view.toolbar.element.appendChild(button);
    }
}