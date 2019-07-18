(function($){
    'use strict';

    $.fn.qiniuav = function(option){
        $(this).each(function(){
            var qiniuav = new Qiniuav($(this));
            qiniuav.init(option);
        });
    };

    function Qiniuav(container){
        this.container = container;
        this.buttonDom = this.container.find('.select-button');
        this.fileDom = this.container.find('input[type=file]');
        this.tokenUrl = this.container.data('url');
        this.checkTranscodeUrl = this.container.data('checktranscode');
        this.loadingUrl = this.container.data('loading');
        this.field = this.container.data('field');
        this.table = this.container.find('.table');
        this.boardContainer = this.container.find('.fsUploadProgress');
        this.boards = [];
        this.multiple = null;
        this.option = null;
    }

    Qiniuav.prototype.init = function(option){
        this.option = option;
        this.multiple = typeof(this.container.attr('multiple')) != 'undefined';

        var that = this;
        this.buttonDom.on('click', this.container, function(){
            that.fileDom.trigger('click');
        });

        for(var i = 0; i < this.option.files.length; i++ ){
            this.table.show();
            var board = this.createBoard(this.option.files[i]);
            this.boards.push(board);
            this.boardContainer.append(board.dom);
        }

        this.fileDom.on('change', this.container, function(){
            var file = this.files[0];
            fetch(that.tokenUrl).then(function(res){
                if(res.ok){
                    return res.json();
                }
                else{
                    throw 'something go error, status:' . res.status;
                }
            }).then(function(data){
                var token = data.token;
                var config = {
                };
                var putExtra = {
                    key:'',
                    fname:'',
                    params: {
                        'x:type': data.type
                    }
                };
                var ext = file.name.substr(file.name.lastIndexOf(".")+1);
                putExtra.key = data.key + (ext ? '.' + ext : '' );
                if(file){
                    that.table.show();
                    if(!that.multiple){
                        that.deleteBoard();
                    }

                    var board = that.createUploadBoard(file, putExtra, token, config);
                    that.boards.push(board);
                    that.boardContainer.append(board.dom);
                }
            });
        });


    };


    Qiniuav.prototype.createUploadBoard = function(file, putExtra, token, config){
        var board = new Board(this, this.boards.length, file.name, file.size, 0, '', 'upload');
        board.observable = qiniu.upload(file, putExtra.key, token, putExtra, config);
        return board;
    };

    Qiniuav.prototype.createBoard = function(fileObj){
        var board;
        if(fileObj.transcode == 1){
            board = new Board(this, this.boards.length, fileObj.name, fileObj.size, fileObj.id, fileObj.url, 'view');
        }
        else{
            board = new Board(this, this.boards.length, fileObj.name, fileObj.size, fileObj.id, '', 'transcode');
        }

        return board;
    }

    Qiniuav.prototype.deleteBoard = function(boardId){
        if(typeof(boardId) == 'undefined'){
            for(var i=0; i < this.boards.length; i++){
                if(typeof(this.boards[i]) == 'undefined'){
                    continue;
                }

                this.boards[i].destroy();
            }
            this.boards = [];
            this.fileDom.val('');
        }
        else{
            this.boards[boardId].destroy();
            delete this.boards[boardId];

            if(this.boardContainer.children().length == 0){
                this.table.hide();
            }
        }
    };

    //type : upload | transcode | view
    function Board(qiniuav,index, filename, filesize, file_id, file_url, type){
        var BLOCK_SIZE = 4 * 1024 * 1024;

        this.qiniuav = qiniuav;
        this.id = index;
        this.file_id = file_id;
        this.filename = filename;
        this.filesize = filesize;
        this.file_url = file_url;
        this.observable = null;
        this.subscription = null;
        this.chunkNum = Math.ceil(filesize / BLOCK_SIZE);
        this.ui = "<td>" +
            "<input type='hidden' class='field-hidden' name='' />" +
            "<span class='filename'>" + filename + "</span>" +
            "<div class='wraper'><a class='linkWrapper'></a></div>" +
            "</td>" +
            "<td class='size'>" +
            filesize +
            "</td>" +
            "<td><div style='overflow:hidden;position:relative' class='detail-container'><div class='totalBar' style='float:left;width:80%;height:30px;border:1px solid;border-radius:3px'>" +
            "<div class='totalBarColor' style='width:0;border:0;background-color:rgba(232,152,39,0.8);height:28px;'></div>" +
            "<p class='speed'></p>" +
            "</div>" +
            "<div class='control-container'>" +
            '<button class="btn btn-default control-upload" type="button">开始上传</button>' +
            "</div></div>" +
            "<div><button class='btn btn-default resume' type=\"button\">查看分块进度</button></div>" +
            "<ul class='fragment-group hide'>" +
            "</ul></td>" +
            "<td class='opration-container'><button class=\"btn btn-danger delete confirm\" style='margin-left: 5px;' type=\"button\">删除</button></td>";
        this.dom = null;
        this.resumeBtn = null;
        this.controlBtn = null;
        this.controlStat = '';
        this.totalBarText = null;
        this.totalBar = null;
        this.totalBarContainer = null;
        this.detailContainer = null;
        this.deleteBtn = null;
        this.oprationContainer = null;
        this.fileInput = null;
        this.checkTranscode = null;
        this.sizeDom = null;
        this.filenameDom = null;

        this.chunkList = [];
        this.chunkListDom = null;

        this.finishedChunkList = [];
        this.init(type);
    };

    Board.prototype.init = function(type){
        this.dom = $(document.createElement("tr"));
        this.dom.html(this.ui);
        this.resumeBtn = this.dom.find('.resume');
        this.controlBtn = this.dom.find('.control-upload');
        this.controlStat = 'ready';
        this.chunkListDom = this.dom.find('.fragment-group');
        this.totalBarText = this.dom.find('.speed');
        this.totalBar = this.dom.find('.totalBarColor');
        this.totalBarContainer = this.dom.find('.totalBar');
        this.detailContainer = this.dom.find('.detail-container');
        this.deleteBtn = this.dom.find('.delete');
        this.fileInput = this.dom.find('.field-hidden');
        this.oprationContainer = this.dom.find('.opration-container');
        this.sizeDom = this.dom.find('.size');
        this.filenameDom = this.dom.find('.filename');

        if(this.qiniuav.multiple){
            this.fileInput.attr('name', this.qiniuav.field + "[]");
        }
        else{
            this.fileInput.attr('name', this.qiniuav.field);
        }

        var board = this;
        switch(type){
            case 'upload':
                if(this.chunkNum > 1){
                    for(var i =0; i < this.chunkNum; i++){
                        this.createChunk();
                    }
                }
                else{
                    this.resumeBtn.addClass('hide');
                }


                this.resumeBtn.on('click', this.dom, function(){
                    if (board.chunkListDom.hasClass("hide")) {
                        board.chunkListDom.removeClass("hide");
                    } else {
                        board.chunkListDom.addClass("hide");
                    }
                });

                this.controlBtn.on('click', this.dom, function(){
                    switch(board.controlStat){
                        case "ready":
                            board.upload();
                            break;
                        case "uploading":
                            board.pause();
                            break;
                    }
                });
                break;
            case 'transcode':
                this.resumeBtn.addClass('hide');
                this.fileInput.val(this.file_id);
                this.transcoding();
                break;
            case 'view':
                this.resumeBtn.addClass('hide');
                this.fileInput.val(this.file_id);
                this.finishTranscode(this.file_url, this.filesize);
                break;
            default:
                throw "unknow type value:" + type;
        }



        this.deleteBtn.on('click', this.dom, function(){
            board.qiniuav.deleteBoard(board.id);
        });
    }

    Board.prototype.initCbConfig = function(){
        var board = this;
        return {
            next: function(response) {
                var chunks = response.chunks||[];
                var total = response.total;
                board.refresh(total.percent, chunks);
            },
            error: function(err) {
                board.setError(err.message);
            },
            complete: function(res) {
                board.fileInput.val(res.file_id);
                board.file_id = res.file_id;
                if(res.ref_id){
                    board.transcoding();
                }
                else{
                    board.setComplete(res.info);
                }
            }
        };
    }

    Board.prototype.transcoding = function(){
        var ui = "<span>转码中</span><img src='"+ this.qiniuav.loadingUrl + "' style='width: 35px;position: absolute;top: -7px;' />";
        this.detailContainer.html(ui);

        var board = this;
        this.checkTranscode = setInterval(function(){
            fetch(board.qiniuav.checkTranscodeUrl + "?file_id=" + board.file_id)
                .then(function(res){
                    if(res.ok){
                        return res.json();
                    }
                    else{
                        throw 'something go error, status:' . res.status;
                    }
                })
                .then(function(data){
                    if(data.status == 1){
                        window.clearInterval(board.checkTranscode);
                        board.finishTranscode(data.url, data.size, data.name);
                    }
                    else if(data.status == 2){
                        window.clearInterval(board.checkTranscode);
                        board.errorTranscode(data.error);
                    }
                });
        }, 2000);
    }

    Board.prototype.errorTranscode = function(error){
        this.detailContainer.html("<p>"+error+"</p>");
    }

    Board.prototype.finishTranscode = function(url, size, filename){
        this.detailContainer.html("<p>"+url+"</p>");
        this.sizeDom.text(size);
        this.filenameDom.text(filename);
        if(this.qiniuav.option.play.name){
            this.oprationContainer.prepend("<button type='button' class=\"btn btn-success play\">" + this.qiniuav.option.play.name + "</button>");
            var board = this;
            this.oprationContainer.find('.play').on('click', this.oprationContainer, function(){
                board.qiniuav.option.play.action(url);
            });
        }
    }


    Board.prototype.upload = function(){
        this.setUploading();
        this.subscription = this.observable.subscribe(this.initCbConfig());
    }

    Board.prototype.pause = function(){
        this.setReady();
        this.subscription.unsubscribe();
    }

    Board.prototype.setError = function(errMsg){
        this.controlStat = 'error';
        this.detailContainer.text(errMsg);
    }

    Board.prototype.setUploading = function(){
        this.controlStat = 'uploading';
        this.controlBtn.text('暂停上传');
    }

    Board.prototype.setReady = function(){
        this.controlStat = 'ready';
        this.controlBtn.text('继续上传');
    }

    Board.prototype.setComplete = function(text){
        this.controlStat = 'complete';
        this.totalBarContainer.addClass('hide');
        this.detailContainer.html(text);
    }

    Board.prototype.destroy = function(){
        this.dom.remove();
        if(this.subscription){
            this.subscription.unsubscribe();
            this.subscription = null;
        }

        this.observable = null;
        this.resumeBtn = null;
        this.controlBtn = null;
        this.controlStat = '';
        this.totalBarText = null;
        this.totalBar = null;
        this.totalBarContainer = null;
        this.detailContainer = null;
        this.deleteBtn = null;
        this.fileInput = null;
        this.oprationContainer = null;
        this.sizeDom = null;

        this.chunkList = [];
        this.chunkListDom = null;

        this.finishedChunkList = [];
        this.dom = null;
    }

    Board.prototype.refresh = function(totalPercent, chunks){
        for (var i = 0; i < chunks.length; i++) {
            if (chunks[i].percent === 0 || this.finishedChunkList[i]){
                continue;
            }
            if (this.chunkList[i].percent === chunks[i].percent){
                continue;
            }
            if (chunks[i].percent === 100){
                this.finishedChunkList[i] = true;
            }
            this.chunkList[i].refresh(chunks[i].percent);
        }

        this.totalBarText.text("进度：" + totalPercent + "% ");
        this.totalBar.css('width', totalPercent + '%');
    }

    Board.prototype.createChunk = function(){
        var chunk = new Chunk();
        this.chunkList.push(chunk);
        this.chunkListDom.append(chunk.dom);
    }


    function Chunk(){
        this.ui = "<div class='childBar' style='width:100%;height:20px;border:1px solid;border-radius:3px'>" +
            "<div class='childBarColor' style='width:0;border:0;background-color:rgba(250,59,127,0.8);height:18px;'>" +
            "</div>" +
            "</div>";
        this.dom = null;
        this.percent = 0;
        this.init();
    }

    Chunk.prototype.init = function(){
        this.dom = document.createElement("li");
        this.dom = $(this.dom);
        this.dom.addClass("fragment");
        this.dom.html(this.ui);
    }

    Chunk.prototype.refresh = function(percent){
        this.percent = percent;
        this.dom.find('.childBarColor').css("width", percent + "%");
    };


}(jQuery));