// ** I18N

// Calendar KON language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Translation: Yourim Yi <yyi@yourim.net>
// Encoding: utf8
// lang : ko
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names

Calendar._DN = new Array
("老夸老",
 "岿夸老",
 "拳夸老",
 "荐夸老",
 "格夸老",
 "陛夸老",
 "配夸老",
 "老夸老");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("老",
 "岿",
 "拳",
 "荐",
 "格",
 "陛",
 "配",
 "老");

// full month names
Calendar._MN = new Array
("1岿",
 "2岿",
 "3岿",
 "4岿",
 "5岿",
 "6岿",
 "7岿",
 "8岿",
 "9岿",
 "10岿",
 "11岿",
 "12岿");

// short month names
Calendar._SMN = new Array
("1",
 "2",
 "3",
 "4",
 "5",
 "6",
 "7",
 "8",
 "9",
 "10",
 "11",
 "12");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "calendar 俊 措秦辑";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"\n"+
"弥脚 滚傈阑 罐栏矫妨搁 http://www.dynarch.com/projects/calendar/ 俊 规巩窍技夸\n" +
"\n"+
"GNU LGPL 扼捞季胶肺 硅器邓聪促. \n"+
"扼捞季胶俊 措茄 磊技茄 郴侩篮 http://gnu.org/licenses/lgpl.html 阑 佬栏技夸." +
"\n\n" +
"朝楼 急琶:\n" +
"- 楷档甫 急琶窍妨搁 \xab, \xbb 滚瓢阑 荤侩钦聪促\n" +
"- 崔阑 急琶窍妨搁 " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " 滚瓢阑 穿福技夸\n" +
"- 拌加 穿福绊 乐栏搁 困 蔼甸阑 狐福霸 急琶窍角 荐 乐嚼聪促.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"矫埃 急琶:\n" +
"- 付快胶肺 穿福搁 矫埃捞 刘啊钦聪促\n" +
"- Shift 虐客 窃膊 穿福搁 皑家钦聪促\n" +
"- 穿弗 惑怕俊辑 付快胶甫 框流捞搁 粱 歹 狐福霸 蔼捞 函钦聪促.\n";

Calendar._TT["PREV_YEAR"] = "瘤抄 秦 (辨霸 穿福搁 格废)";
Calendar._TT["PREV_MONTH"] = "瘤抄 崔 (辨霸 穿福搁 格废)";
Calendar._TT["GO_TODAY"] = "坷疵 朝楼肺";
Calendar._TT["NEXT_MONTH"] = "促澜 崔 (辨霸 穿福搁 格废)";
Calendar._TT["NEXT_YEAR"] = "促澜 秦 (辨霸 穿福搁 格废)";
Calendar._TT["SEL_DATE"] = "朝楼甫 急琶窍技夸";
Calendar._TT["DRAG_TO_MOVE"] = "付快胶 靛贰弊肺 捞悼 窍技夸";
Calendar._TT["PART_TODAY"] = " (坷疵)";
Calendar._TT["MON_FIRST"] = "岿夸老阑 茄 林狼 矫累 夸老肺";
Calendar._TT["SUN_FIRST"] = "老夸老阑 茄 林狼 矫累 夸老肺";
Calendar._TT["CLOSE"] = "摧扁";
Calendar._TT["TODAY"] = "坷疵";
Calendar._TT["TIME_PART"] = "(Shift-)努腐 肚绰 靛贰弊 窍技夸";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%b/%e [%a]";

Calendar._TT["WK"] = "林";
