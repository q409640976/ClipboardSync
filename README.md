# 多设备剪切板同步工具

## 概述
多台电脑(及手机)剪切板同步，通过 AES 加密后，使用 PHP 服务端作为中转，客户端解密，确保云端数据安全。

## 使用场景
- 多台电脑需要剪切板同步的
- 哪怕一台电脑，需要云备份历史剪切板的

## 编程语言
- **客户端**：Rust（性能极佳）
- **服务器**：PHP + MySQL（部署门槛低）

## 实现功能
- ✅ 客户端 AES 加密上传至中转服务器  
- ✅ 客户端下载加密数据并本地解密  
- ✅ 自动写入 Ditto、CopyQ 历史记录  
- ✅ 同步参数可控

## 开源状态
- **客户端**：开源  
- **服务端**：闭源

## 免费版
- 满足大部分日常使用需求
- 单条内容最大长度：10 万字

## 高级版
- 支持图片，微信：`pctongbu`


## 部署步骤
- php源码上传服务器,并配置数据库连接
- 手工在数据库添加客户端白名单:表`clients`
- 下载客户端,以及config.txt,并修改服务端网址(注意以"/"结尾)

## 与手机同步方案
- 用到Android神器【HTTP Shortcuts】(https://http-shortcuts.rmy.ch/  )
- 一键获取PC剪切板,发送手机剪切板到PC
- 注意（getLastOne.php 和 getLastOne.php记得改名）
- 导入脚本【shortcuts脚本导入.zip】，然后修改参数

## 下载
👉 [Github下载](https://github.com/q409640976/ClipboardSync/releases)
👉 [国内下载](https://gitee.com/q409640976/ClipboardSync/releases/)



