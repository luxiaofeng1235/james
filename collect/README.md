泡书吧-V1.0
===============
应用目录
gather_list.php ##同步小说的基本信息列表
gather_info.php ##同步小说的基本详情信息+章节目录
gather_sync.php 按照小说的已生成的列表数据同步批量同步小说详情
用法：
gather_syc.php 中会调用gather_info.php +小说ID信息
===============================
syc_folder.php 同步小说生成的目录
syc_content.php 同步生成小说的内容信息到本地


采集用到的脚本：
local_file.php +需要处理的id信息，会自动爬取对应的数据信息（采用分批次的设计程序）