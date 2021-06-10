#!/bin/awk -f
# 文件名: members.awk
# 执行命令: awk -f members.awk two_members.txt > updateMembers.sql
BEGIN{
	pat="^[A-Z]{1}[0-9]{13}$"
}

{
	if (NR > 1) {
		printf "-- APP昵称： %s, PC端昵称： %s\n", $4, $10
		if ($4 !~ pat && $10 ~ pat) {
			# 手机端修改昵称，pc端未修改
			printf "update pre_common_member set sso_id=%d WHERE uid=%d;\n",$9,$2
			printf "update pre_common_member set sso_id=0 WHERE uid=%d;\n",$8
		} else {
			printf "update pre_common_member_map set uid=%d where umid=%d and uid=%d;\n",$8,$1,$2
		}	
	}
}
