(function($){
    const getFileType ={
        fileToArrayBuffer:function f(file, callback){
            const reader = new FileReader()
            reader.onload = function(evt){
                if(typeof callback === 'function') {
                    return callback(evt.target.result)
                }
            }
            reader.readAsArrayBuffer(file);
        },
        getFileTypeViaHeader:function f(e) {
            const bufferInt = new Uint8Array(e);

            const arr = bufferInt.slice(0, 4);  // 通用格式图片
            const headerArr = bufferInt.slice(0, 16);  // heic格式图片
            let header = '';
            let allHeader = '';
            let realMimeType;

            for (let i = 0; i < arr.length; i++) {
                header += arr[i].toString(16); // 转成16进制的buffer
            }

            for (let i = 0; i < headerArr.length; i++) {
                allHeader += headerArr[i].toString(16);
            }
            // magic numbers: http://www.garykessler.net/library/file_sigs.html
            // console.log(header.indexOf('000'),allHeader.lastIndexOf('000'))
            switch (header) {
                case '89504e47':
                    realMimeType = 'image/png';
                    break;
                case '47494638':
                    realMimeType = 'image/gif';
                    break;
                case 'ffd8ffDB':
                case 'ffd8ffe0':
                case 'ffd8ffe1':
                case 'ffd8ffe2':
                case 'ffd8ffe3':
                case 'ffd8ffe8':
                    realMimeType = 'image/jpeg';
                    break;
                case '00020':  // heic开头前4位可能是00020也可能是00018，其实这里应该是判断头尾000的，可以自己改下
                case '00018':
                case '00024':
                case '0001c':
                    (allHeader.lastIndexOf('000') === 22) ? (realMimeType = 'image/heic') : (realMimeType = 'unknown');
                    break;
                default:
                    realMimeType = 'unknown';
                    break;
            }
            return realMimeType;
        },
        start:function f(file, cb){
            const thisObj = this;
            thisObj.fileToArrayBuffer(file,function (res) {
                const type = thisObj.getFileTypeViaHeader(res);
                cb(type);
            });
        }
    }

    const injectFileProp ={
        setNative: function (file){
            if (!file.hasOwnProperty("getNative")){
                file.getNative = function (){
                    return file;
                }
            }
        },
        setHashId: function(file,need_cacl, finish){
            const selfObj = this;
            if (need_cacl){
                window.calc_file_hash(file.getNative()).then(function(res){
                    file.hash_id = res;
                    finish(file)
                });
            }else{
                file.hash_id = '';
                finish(file)
            }
        },
        setFileType: function(file,need_cacl, finish){
            if(!finish || typeof finish !== 'function'){
                throw new Error("finish callback is not exists!")
            }
            const selfObj = this;
            selfObj.setNative(file)
            if (file.type === ''){
                getFileType.start(file.getNative(), function f(type){
                    if (type === 'image/heic'){
                        file.type = type;
                    }
                    selfObj.setHashId(file,need_cacl, finish)
                })
            }else{
                selfObj.setHashId(file,need_cacl, finish)
            }
        }
    }

    window.qsFileHelper = {
        getFileType: getFileType,
        injectFileProp: injectFileProp
    }


})(jQuery)


