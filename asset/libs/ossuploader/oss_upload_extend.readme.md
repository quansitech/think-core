## oss_upload_extend oss上传扩展

### 介绍

基于oss上传插件，对上传插件的回调进行扩展，增强oss插件上传功能

### 功能：

* 可以组合添加拓展
* 可以对扩展进行排序
* <a href="#preventUpload">内置preventUpload扩展</a>
* 根据oss_upload的回调,添加各自的回调


### 初始化调用

```console
$.fn.ossuploaderWrapper(option[, extend]);
option: 原oss上传插件的option
extend: 扩展名
```
更多扩展: <a href="#extend_desc">扩展介绍</a>


## 扩展说明
### 执行逻辑
* 根据传入的extend进行排序
* 把extend遍历,从conf中取出要扩展的配置
* 把配置中的回调放到相应的队列中
* 当触发相应的回调,则执行相应队列中的函数 
* 若队列中有函数返回false,则队列后面的函数都不执行

### <a name="extend_desc">内置扩展介绍</a>

#### <a name="preventUpload">preventUpload</a>(默认扩展)
上传图片的过程中,隐藏域所在的form
* 会禁止submit事件(submit事件禁止且提示图片上传中)
* type为submit 的按钮会显示为上传中，当上传完成会恢复原来的描述
```console
$.fn.ossuploaderWrapper(option, ['preventUpload']);
```

### 添加自定义扩展

conf 对象内置了preventUpload扩展  

往conf对象加属性对象，实现扩展的功能 

```console
var conf = {
    myExtend: {
        invoke: function(){
                return {};
            } || {} //require
        order: number, // >=2,optional
    }
};
```
* invoke 属性必须为<code>返回对象的函数</code>或者<code>对象</code>   
  扩展的回调队列中，任意一个函数返回false,都会停止执行后续回调   
  该对象可包含要扩展的回调,如 beforeUpload,uploadCompleted (按需添加)

* order属性为插件的调用排序,值越小调用顺序越早;  
  由于preventUpload扩展(内置扩展)的order为 1,  
  其他默认内置扩展大于1，
  非默认内置扩展大于100。

