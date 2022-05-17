<?php
/**
 * Description: 设备相关操作函数
 * User: renchen
 * Date: 2021/5/27 19:35
 * Version: $Id: device.func.php 156420 2021-09-13 03:39:02Z duanyc $
 */

/**
 * 获取设备类型（GetPhoneInfo）
 *
 * @param string $userAgent
 *
 * @return array
 */
function getDeviceType(string $userAgent = '')
{
    $a = $userAgent !== '' ? $userAgent : $_SERVER['HTTP_USER_AGENT'];
    cutil_php_debug('user_agent信息：' . $a, 'getphoneinfo');
    $f = "";
    $v = "";
    $o = "";
    if (stripos($a, "Linux") !== false) {
        $o = "Linux";
    }
    if (stripos($a, "SymbianOS") !== false) {
        $o = "Symbian";
    } elseif (stripos($a, "Windows NT") !== false) {
        $o = "Windows";
    } elseif (stripos($a, "Android") !== false) {
        $o = "Android";
    } elseif (stripos($a, "iOS ") !== false) {
        $o = "iOS";
    } elseif (stripos($a, "Windows Phone") !== false) {
        $o = "Windows Phone OS";
    } elseif (stripos($a, "Linux") !== false && stripos($a, "x86_64") !== false) {
        $o = "Linux";
        $v = "Linux";
    } elseif (stripos($a, "Linux") !== false && stripos($a, "loongson") !== false) {
        $o = "Linux";
    } elseif (stripos($a, "Linux") !== false && stripos($a, "mips64") !== false) {
        $o = "Linux";
    } elseif (stripos($a, "Linux") !== false && stripos($a, "aarch64") !== false) {
        $o = "Linux";
    } elseif (stripos($a, "Linux") !== false && stripos($a, "kylin") !== false) {
        $o = "Linux";
    } elseif (stripos($a, "ASM_LinuxClient_AGENT") !== false) {
        $o = "LinuxServer";
    }

    if (stripos($a, "Windows NT") !== false) {
        $f = "windows";
        $v = "windows";
    } elseif (stripos($a, "NOKIA;") !== false) {
        $f = "NOKIA";
        $v = GetSubStr($a, "NOKIA; ", ")");
    } elseif (stripos($a, "iphone") !== false) {
        $f = "apple";
        $v = "iPhone";
    } elseif (stripos($a, "; My Phone") !== false) {//Mozilla/5.0 (Linux; U; Android 4.0.4; zh-CN; My Phone Build/Cain) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/8.8.3.278 U3/0.8.0 Mobile Safari/534.31
        $f = "Windows";
        $v = "Mobile";
    } elseif (stripos($a, "iPhone 4;") !== false || stripos($a, "iPh") !== false) {
        //MQQBrowser/34 Mozilla/5.0 (iPhone 4; CPU iPhone OS
        $f = "apple";
        $v = "iPhone";
        $o = "iPhone";
    } elseif (stripos($a, "SonyEricsson") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; SonyEricssonLT18i Build/4.0.2.A.0.62) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "SonyEricsson";
        $v = GetSubStr($a, "SonyEricsson", " ");
    } elseif (stripos($a, "; GT-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; GT-I9100 Build/GINGERBREAD) UC AppleWebKit/530+ (KHTML, like Gecko) Mobile Safari/530
        $f = "三星";
        $v = GetSubStr($a, "; GT-", " ");
    } elseif (stripos($a, ";GT-") !== false) {
        $f = "三星";
        $v = GetSubStr($a, ";GT-", "Build");
    } elseif (stripos($a, "SAMSUNG-GT-") !== false) {
        //SAMSUNG-GT-I9108_TD/1.0 Android/2.3.3 Release/03.15.2011 Browser/AppleWebKit533.1 Profile/MIDP-2.1 Configuration/CLDC-1.1
        $f = "三星";
        $v = GetSubStr($a, "SAMSUNG-GT-", "_TD");
    } elseif (stripos($a, "-GT-") !== false) {
        //android-17-540x960-GT-I9158
        $f = "三星";
        $v = stripos($a, "-GT-");
    } elseif (stripos($a, "; SAMSUNG-SCH-") !== false) {
        $f = "三星";
        $v = GetSubStr($a, "; SAMSUNG-SCH-", " ");
    } elseif (stripos($a, "; SAMSUNG ") !== false) {
        //Mozilla/5.0 (Linux; Android 4.2.2; zh-cn; SAMSUNG GT-I9508 Build/JDQ39) AppleWebKit/535.19 (KHTML, like Gecko) Version/1.0 Chrome/18.0.1025.308 Mobile Safari/535.19
        $f = "三星";
        $v = GetSubStr($a, "; SAMSUNG ", " ");
    } elseif (stripos($a, "; SCH-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; SCH-N719 Build/JZO54K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "三星";
        $v = "SCH-" . GetSubStr($a, "; SCH-", " ");
    } elseif (stripos($a, "SGH-") !== false) {
        $f = "三星";
        $v = GetSubStr($a, "SGH-", " ");
    } elseif (stripos($a, "SM-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; SCH-N719 Build/JZO54K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "三星";
        $v = GetSubStr($a, "SM-", " ");
    } elseif (stripos($a, "; Galaxy Nexus") !== false) {
        $f = "三星";
        $v = "Nexus";
    } elseif (stripos($a, "Galaxy") !== false) {
        $f = "三星";
        $v = GetSubStr($a, "Galaxy", " ");
    } elseif (stripos($a, "N7100") !== false) {
        $f = "三星";
        $v = 'N7100';
    } elseif (stripos($a, "; I7100") !== false) {
        $f = "三星";
        $v = 'I7100';
    } elseif (stripos($a, "; SHV-") !== false) {
        $f = "三星";
        $v = GetSubStr($a, "; SHV-", " ");
    } elseif (stripos($a, "; SHW-") !== false) {
        $f = "三星";
        $v = GetSubStr($a, "; SHW-", " ");
    } elseif (stripos($a, "NOKIA") !== false) {
        //Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 NOKIA5800w/50.0.005; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.2.3
        $f = "NOKIA";
        if ($a === "NOKIAE63") {
            $v = "E63";
            $o = "Symbian";
        } else {
            $v = GetSubStr($a, "NOKIA", "/");
        }
    } elseif (stripos($a, "iPad") !== false) {
        //Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; zh-cn) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B367 Safari/531.21.10
        $f = "apple";
        $v = "iPad";
    } elseif (stripos($a, "MI-ONE") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; MI-ONE Plus Build/GINGERBREAD) UC AppleWebKit/530+ (KHTML, like Gecko) Mobile Safari/530
        $f = "小米";
        $v = "MI-ONE";
    } elseif (stripos($a, "MI ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; MI 1S
        $f = "小米";
        $v = "MI " . GetSubStr($a, "MI ", " ");
    } elseif (stripos($a, "Xiaomi") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; MI 1S
        $f = "小米";
        $v = "V1";
        //Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/61.0.3163.128 Safari/534.24 XiaoMi/MiuiBrowser/10.3.2 小米手机被识别成了linuxpc yanzj 20181212
        if (stripos($a, 'X11;') !== false && stripos($a, 'AppleWebKit/534.24') !== false && stripos($a, 'XiaoMi/MiuiBrowser/10.3.2') !== false) {
            $o = "Android";
        }
    } elseif (stripos($a, "; M3 ") !== false) {
        $f = "小米";
        $v = "M3";
    } elseif (stripos($a, "; 2013022 ") !== false) {
        $f = "红米";
        $v = "2013022";
    } elseif (stripos($a, "; MEIZU") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; MEIZU
        $f = "魅族";
        $v = "MEIZU";
    } elseif (stripos($a, "; MZ-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; MEIZU
        $f = "魅族";
        $v = GetSubStr($a, "; MZ-", " ");
    } elseif (stripos($a, "; MX5") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; MEIZU
        $f = "魅族";
        $v = "MX5";
    } elseif (stripos($a, "; M9") !== false || stripos($a, "; M040") !== false || stripos($a, "; M045") !== false || stripos($a, "; M030") !== false || stripos($a, "; M353") !== false || stripos($a, "; M351") !== false || stripos($a, "; M356") !== false || stripos($a, "; M6") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; M9 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        //Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; M040 Build/JRO03H) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 yanzj 20180322 增加魅族6的判断方式
        $_v = "M" . GetSubStr($a, "; M", " ");
        if ($_v !== "Mobile;") {
            $f = "魅族";
            $v = $_v;
        }
    } elseif (stripos($a, "; IM-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; IM-A8
        $f = "飞思卡尔";
        $v = GetSubStr($a, "; IM-", " ");
    } elseif (stripos($a, "; Lenovo") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; Lenovo A520/S101) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "联想";
        $v = str_replace(["Build", ""], "", GetSubStr($a, "; Lenovo", "/"));
    } elseif (stripos($a, "/Lenovo") !== false) {
        //Lenovo A298t_TD/1.0 Linux/2.6.35.7 Android 2.3.5 Release/04.07.2013 Browser/AppleWebKit533.1 (KHTML, like Gecko) Mozilla/5.0 Mobile
        $f = "联想";
        $v = "Lenovo " . GetSubStr($a, "Lenovo ", "_");
    } elseif (stripos($a, "; LNV-Lenovo ") !== false) {
        $f = "联想";
        $v = "Lenovo " . GetSubStr($a, "; LNV-Lenovo ", " ");
    } elseif (stripos($a, "Lenovo A") !== false) {
        $f = "联想";
        $v = "Lenovo " . GetSubStr($a, "Lenovo ", "_");
    } elseif (stripos($a, "Lenovo P700iLenovo-") !== false) {
        $f = "联想";
        $v = "Lenovo " . GetSubStr($a, "Lenovo P700iLenovo-", " ");
    } elseif (stripos($a, "Lenovo") !== false) {
        //Lenovo A298t_TD/1.0 Linux/2.6.35.7 Android 2.3.5 Release/04.07.2013 Browser/AppleWebKit533.1 (KHTML, like Gecko) Mozilla/5.0 Mobile
        $f = "联想";
        $v = "Lenovo " . GetSubStr($a, "Lenovo ", " ");
    } elseif (stripos($a, "; S2005A-H") !== false) {
        $f = "联想";
        $v = "S2005A";
    } elseif (stripos($a, "; IdeaTab") !== false) {
        $f = "联想";
        $v = GetSubStr($a, "; IdeaTab", " ");
        $v = $v !== '' ? $v : GetSubStr($a, "; IdeaTab ", " ");
    } elseif (stripos($a, "a850 ") !== false) {
        $f = "联想";
        $v = "a850";
    } elseif (stripos($a, "; S10 ") !== false) {
        $f = "联想";
        $v = "S10";
    } elseif (stripos($a, "; TAB A10-80HC") !== false) {
        $f = "联想";
        $v = "pad A10-80HC";
    } elseif (stripos($a, "; LG-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.2.2; zh-cn; LG-P970 Build/FRG83G) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "LG";
        $v = GetSubStr($a, "; LG-", " ");
    } elseif (stripos($a, "; LG") !== false) {
        $f = "LG";
        $v = GetSubStr($a, "; LG", " ");
    } elseif (stripos($a, "HW-HUAWEI_") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; HW-HUAWEI_C8813/C8813V100R001C92B169; 480*854; CTC/2.0) AppleWebKit/534.30 (KHTML, like Gecko) Mobile Safari/534.30
        $f = "华为";
        $v = GetSubStr($a, "HW-HUAWEI_", "/");
    } elseif (stripos($a, "Huawei_") !== false) {
        //HUAWEI_G520-5000_TD/1.0 Android/4.1.2 (Linux; U; Android 4.1.2; zh-cn) Release/01.31.2013 Browser/WAP2.0 (AppleWebKit/534.30) Mobile Safari/534.30
        $f = "华为";
        $v = GetSubStr($a, "Huawei_", "_TD");
    } elseif (stripos($a, "HuaweiU8836D") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; U8836D Build/HuaweiU8836D) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        $f = "华为";
        $v = "U8836D";
    } elseif (stripos($a, "; HUAWEI") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; HUAWEI Y300-0000 Build/HuaweiY300-0000) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "华为";
        $v = GetSubStr($a, "; HUAWEI ", " ");
    } elseif (stripos($a, "/Huawei") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; T8830Pro Build/HuaweiT8830Pro) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        //Mozilla/5.0 (Linux; Android 8.1.0; EML-AL00 Build/HUAWEIEML-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 Mobile Safari/537.36
        $f = "华为";
        $v = GetSubStr($a, "/Huawei", ")");
        if (stripos($v, ";") !== false) {
            $v = substr($v, 0, strrpos($v, ';'));
        }
    } elseif (stripos($a, "HW-HUAWEI ") !== false) {
        $f = "华为";
        $v = GetSubStr($a, "HW-HUAWEI ", "/");
    } elseif (stripos($a, "HUAWEI ") !== false) {
        $f = "华为";
        $v = GetSubStr($a, "HUAWEI ", " ");
    } elseif (stripos($a, "HW-") !== false) {
        $f = "华为";
        $v = GetSubStr($a, "HW-", "/");
    } elseif (stripos($a, " H60-") !== false) {
        $f = "华为";
        $v = GetSubStr($a, "H60-", " ");
    } elseif (stripos($a, "zh-cn; C8500") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.1-update1; zh-cn; C8500 Build/ERE27; 240*320) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1/UCWEB7.9.4.145/139/800
        $f = "华为";
        $v = "C8500";
    } elseif (stripos($a, "U8812D") !== false) {
        $f = "华为";
        $v = "U8812D";
    } elseif (stripos($a, "; H30-U10") !== false) {
        $f = "华为荣耀";
        $v = "H30-U10";
    } elseif (stripos($a, "HONORBLN") !== false) {
        //Mozilla/5.0 (Linux; U; Android 8.0.0;zh-cn; BLN-AL10 Build/HONORBLN-AL10) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 Chrome/57.0.2987.132 MQQBrowser/8.1 Mobile Safari/537.36
        $f = "华为荣耀";
        $v = GetSubStr($a, "HONORBLN", ")");
    } elseif (stripos($a, "LG_P503/") !== false) {
        //LG_P503/1.0 Android/2.2 Release/5.20.2010 Browser/KHTML (Mozilla/5.0 AppleWebKit/533.1 Version/4.0 Mobile Safari/533.1)
        $f = "LG";
        $v = GetSubStr($a, "LG_", "/");
    } elseif (stripos($a, "; Nexus") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.2.2; zh-CN; Nexus 4 Build/JDQ39) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.2.0.308 U3/0.8.0 Mobile Safari/534.31
        $f = "LG";
        $v = "Nexus " . GetSubStr($a, "; Nexus ", " ");
    } elseif (stripos($a, "ZTE-") !== false && stripos($a, "_TD") !== false) {
        //ZTE-TU880_TD/1.0 Linux/2.6.32 Android/2.2 Release/5.25.2011 Browser/AppleWebKit533.1
        $f = "中兴";
        $v = GetSubStr($a, "ZTE-", "_");
    } elseif (stripos($a, "; ZTE-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.2.2; zh-cn; ZTE-T U830 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1; 360browser(securitypay,securityinstalled); 360(android,uppayplugin); 360 Aphone Browser (4.7.1)
        $f = "中兴";
        $v = "ZTE-" . GetSubStr($a, "; ZTE-", " ");
    } elseif (stripos($a, "; ZTE") !== false) {
        //JUC (Linux; U; 2.3.7; zh-cn; ZTE U880E; 480*800) U
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; ZTE U795+ Build/IMM76D) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        $f = "中兴";
        $v = GetSubStr($a, "; ZTE ", " ");
    } elseif (stripos($a, "ZTE") !== false) {
        //ZTEU795_TD/1.0 Linux/2.6.39 Android/4.0 Release/6.10.2012 Browser/AppleWebKit534.30 baidubrowser/3.1.6.4 (Baidu; P1 4.0.4)
        $f = "中兴";
        $v = "ZTE" . str_replace(["_TD", " "], "", GetSubStr($a, "ZTE", "/"));
    } elseif (stripos($a, "MOT-") !== false) {
        //MOT-ME860/1.0 Mozilla/5.0 Android/2.3.5 Release/6.
        $f = "摩托罗拉";
        $v = GetSubStr($a, "MOT-", "/");
    } elseif (stripos($a, "; MT") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; MT917 Build/6.7.2_GC-205-DNRTD-4) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        //Mozilla/5.0 (Linux; U; Android 2.2.2; zh-CN; MT620 Build/THTTD_N_01.01.36I) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        //UCWEB/2.0 (Linux; U; Adr 2.2.2; zh-CN; MT620) U2/1.0.0 UCBrowser/9.1.1.309 U2/1.0.0 Mobile
        $f = "摩托罗拉";
        $v = str_replace(")", "", "MT" . GetSubStr($a, "; MT", " "));
    } elseif (stripos($a, "; M032 ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; M032 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "摩托罗拉";
        $v = "M032";
    } elseif (stripos($a, "; MB") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; MB865 Build/JRO03C) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.3 Mobile Safari/533.1
        $f = "摩托罗拉";
        $v = "MB" . GetSubStr($a, "; MB", " ");
    } elseif (stripos($a, "zh-cn; ME") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.2.2; zh-cn; ME525 Build/JDGC_2.10.0) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1; 360browser(securitypay,securityinstalled); 360(android,uppayplugin); 360 Aphone Browser (4.7.2)
        $f = "摩托罗拉";
        $v = "ME" . GetSubStr($a, "zh-cn; ME", " ");
    } elseif (stripos($a, "; XT") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; XT882 Build/SWDFS_M7_4.80.0) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "摩托罗拉";
        $v = "XT" . GetSubStr($a, "; XT", " ");
    } elseif (stripos($a, "; Moto_") !== false) {
        $f = "摩托罗拉";
        $v = GetSubStr($a, "; Moto_", " ");
    } elseif (stripos($a, "; V8") !== false || stripos($a, "; V3") !== false) {
        $f = "摩托罗拉";
        $v = "V";
    } elseif (stripos($a, "K-Touch_") !== false) {
        //K-Touch_T619/960211_8510_V0101 Mozilla/5.0 (Linux;
        $f = "天语";
        $v = GetSubStr($a, "K-Touch_", "/");
    } elseif (stripos($a, "; K-Touch ") !== false) {
        //K-Touch_T619/960211_8510_V0101 Mozilla/5.0 (Linux;
        $f = "天语";
        $v = GetSubStr($a, "; K-Touch ", " ");
    } elseif (stripos($a, "K-Touch") !== false) {
        //K-TouchC986t_TD/1.0 Android 4.0.3 Release/10.01.2012 Browser/WAP2.0 appleWebkit/534.30
        $f = "天语";
        $v = GetSubStr($a, "K-Touch", "_TD");
    } elseif (stripos($a, "; T780+") !== false) {
        $f = "天语";
        $v = "T780";
    } elseif (stripos($a, "YL-Coolpad ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; YL-Coolpad 5210S Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "酷派";
        $v = GetSubStr($a, "YL-Coolpad ", " ");
    } elseif (stripos($a, "YL-Coolpad_") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; YL-Coolpad_5870/4.0.005.120418.5870; 480*800; CTC/2.0) CoolpadWebkit/534.30
        $f = "酷派";
        $v = GetSubStr($a, "YL-Coolpad_", "/");
    } elseif (stripos($a, "; CoolPad ") !== false) {

        $f = "酷派";
        $v = GetSubStr($a, "; CoolPad ", " ");
    } elseif (preg_match_all("/.*; (\d{4}) .*/", $a, $out)) {
        //Mozilla/5.0 (Linux; U; Android 2.2.2; zh-CN; 8810 Build/FRG83G) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.0.297 U3/0.8.0 Mobile Safari/534.31
        $f = "酷派";
        $v = $out[1][0];
    } elseif (stripos($a, "; Coolpad") !== false) {
        //UCWEB/2.0 (Linux; U; Adr 2.3.5; zh-CN; Coolpad8050) U2/1.0.0 UCBrowser/9.1.1.309 U2/1.0.0 Mobile
        $f = "酷派";
        $v = GetSubStr($a, "; Coolpad", " ");
    } elseif (stripos($a, "CoolPad") !== false) {
        //CoolPad8150_CMCC_TD/1.0 Linux/2.6.35 Android/2.3 Release/12.25.2011 Browser/AppleWebkit533.1
        $f = "酷派";
        $v = GetSubStr($a, "CoolPad", "_");
    } elseif (stripos($a, "5860S") !== false) {
        $f = "酷派";
        $v = "5860S";
    } elseif (stripos($a, "; E2001") !== false) {
        $f = "酷派";
        $v = "E2001";
    } elseif (stripos($a, "A953") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; A953 Build/MIUI) UC AppleWebKit/530+ (KHTML, like Gecko) Mobile Safari/530
        $f = "MOTO";
        $v = "A953";
    } elseif (stripos($a, "Mac OS X") !== false) {
        //Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:10.0.1) Gecko/20100101 Firefox/10.0.1
        $f = "apple";
        $v = "Mac";
        $o = "MacOS";
    } elseif (stripos($a, "MacOS") !== false) {
        // yanzj 20180322 mac客户端上报上来的xml报文用os中的MacOS来坐唯一标识
        //<MSAC><DeviceName>apple-Mac</DeviceName><OS>MacOS</OS><Ip>172.31.86.236</Ip>
        $f = "apple";
        $v = "Mac";
        $o = "MacOS";
    } elseif (stripos($a, "iPad") !== false) {
        //Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:10.0.1) Gecko/20100101 Firefox/10.0.1
        $f = "apple";
        $v = "iPad";
        $o = "iPad";
    } elseif (stripos($a, "; HTC Magic") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; HTC Magic Build/IMM76D) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        $f = "HTC";
        $v = "A6188";
    } elseif (stripos($a, "; HTC Hero") !== false) {
        $f = "HTC";
        $v = "A6288";
    } elseif (stripos($a, "; HTC Tattoo") !== false) {
        $f = "HTC";
        $v = "A3288";
    } elseif (stripos($a, "; HTC Dragon") !== false) {
        $f = "HTC";
        $v = "nexus one";
    } elseif (stripos($a, "; HTC Legend") !== false) {
        $f = "HTC";
        $v = "A6363";
    } elseif (stripos($a, "; HTC Desirre") !== false) {
        $f = "HTC";
        $v = "A8180";
    } elseif (stripos($a, "; HTC Wildfire") !== false) {
        $f = "HTC";
        $v = "A3333";
    } elseif (stripos($a, "; HTC Aria") !== false) {
        $f = "HTC";
        $v = "A6380";
    } elseif (stripos($a, "; HTC Desire HD") !== false) {
        $f = "HTC";
        $v = "A9191";
    } elseif (stripos($a, "; HTC Incredible S") !== false) {
        $f = "HTC";
        $v = "A710e";
    } elseif (stripos($a, "; HTC Desire S") !== false) {
        $f = "HTC";
        $v = "S510e";
    } elseif (stripos($a, "; HTC Wildfire S") !== false) {
        $f = "HTC";
        $v = "A510e";
    } elseif (stripos($a, "HTC Sensation") !== false) {
        $f = "HTC";
        $v = GetSubStr($a, "HTC Sensation ", " ");
    } elseif (stripos($a, "; HTC Salsa") !== false) {
        $f = "HTC";
        $v = "C510e";
    } elseif (stripos($a, "; HTC ChaCha") !== false) {
        $f = "HTC";
        $v = "A810e";
    } elseif (stripos($a, "; HTC EVO 3D") !== false) {
        $f = "HTC";
        $v = "X515m";
    } elseif (stripos($a, "; HTC Sensation Xe") !== false) {
        $f = "HTC";
        $v = "Z715 XE";
    } elseif (stripos($a, "; HTC Raider 4G") !== false) {
        $f = "HTC";
        $v = "X710";
    } elseif (stripos($a, "; HTC Rhyme") !== false) {
        $f = "HTC";
        $v = "S510b";
    } elseif (stripos($a, "; HTC Sensation XL") !== false) {
        $f = "HTC";
        $v = "X315e";
    } elseif (stripos($a, "HTC_") !== false) {
        //HTC_P3700 Mozilla/4.0 (compatible; MSIE 6.0; Windows CE; IEMobile 7.11)
        $f = "HTC";
        $v = GetSubStr($a, "HTC_", " ");
    } elseif (stripos($a, "; HTC-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; HTC-T328d/1.55.1401.2) AndroidWebKit/534.30 (KHTML, Like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "HTC";
        $v = GetSubStr($a, "; HTC-", "/");
    } elseif (stripos($a, "; HTC ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; HTC T328d Build/IML74K) UC AppleWebKit/530+ (KHTML, like Gecko) Mobile Safari/530
        $f = "HTC";
        $v = GetSubStr($a, "; HTC ", " ");
    } elseif (stripos($a, "; Desire HD") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.6; zh-cn; Desire HD Build/HuaweiU8860) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        $f = "HTC";
        $v = "Desire HD";
    } elseif (stripos($a, "; Incredible") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.6; zh-cn; Desire HD Build/HuaweiU8860) UC AppleWebKit/534.31 (KHTML, like Gecko) Mobile Safari/534.31
        $f = "HTC惊艳";
        $v = "Incredible";
    } elseif (stripos($a, "; Amaze_4G") !== false) {
        $f = "HTC";
        $v = "Amaze";
    } elseif (stripos($a, "; PG86100") !== false) {
        $f = "HTC";
        $v = "PG86100";
    } elseif (stripos($a, "HTCSensation") !== false) {
        $f = "HTC";
        $v = "Sensation";
    } elseif (stripos($a, "HTC") !== false) {
        //HTCT328t_TD/1.0 Android/4.0 release/2012 Browser/WAP2.0 Profile/MIDP-2.0 Configuration/CLDC-1.1
        $f = "HTC";
        $v = GetSubStr($a, "HTC", "_TD");
        $v = $v !== '' ? $v : stripos($a, "HTC ");
        $v = $v !== '' ? $v : GetSubStr($a, "HTC; ", ")");
    } elseif (preg_match_all("/.*; (\w{1}\d{3}\w{1}) .*/", $a, $out) && stripos($a, "OPPO") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; U705T Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 baiduboxapp/042_8.3_diordna_888_045/OPPO_51_4.0.4_T507U/1000335b/106006E2D71EF4BE359F9595F0E2881B%7C651322910622968/1
        $f = "OPPO";
        $v = $out[1][0];
    } elseif (stripos($a, "OPPO_") !== false) {
        //OPPO_R817T/1.0 Linux/3.0.35.7 Android/4.0 Release/11.15.2012 Browser/AppleWebKit534.30 Mobile Safari/534.30 MBBMS/2.2
        $f = "OPPO";
        $v = GetSubStr($a, "OPPO_", "/");
    } elseif (stripos($a, "; X905") !== false) {
        //Dalvik/1.4.0 (Linux; U; Android 2.3.6; X905 Build/GRK39F)
        $f = "OPPO";
        $v = "X905";
    } elseif (stripos($a, "; OPPO") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; OPPOX907 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; OPPOT29 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "OPPO";
        $v = GetSubStr($a, "OPPO", " ");
    } elseif (stripos($a, "; T29") !== false || stripos($a, "; T703") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; T29 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "OPPO";
        $v = "T" . GetSubStr($a, "; T", " ");
    } elseif (stripos($a, "; R8") !== false) {
        //UCWEB/2.0 (Linux; U; Adr 2.3.6; zh-CN; R817T) U2/1.0.0 UCBrowser/9.1.1.309 U2/1.0.0 Mobile
        $f = "OPPO";
        $v = 'R' . GetSubStr($a, "; R", " ");
    } elseif (stripos($a, "; X") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-CN; X907 Build/IML74K) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.0.0.282 U3/0.8.0 Mobile Safari/534.31
        $f = "OPPO";
        $v = "X" . str_replace([" ", "_"], "", GetSubStr($a, "; X", " B"));
    } elseif (stripos($a, "; U70") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-CN; U705T Build/IMM76D) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        $f = "OPPO";
        $v = "U70" . GetSubStr($a, "; U70", " ");
    } elseif (stripos($a, "; N1T ") !== false) {
        $f = "OPPO";
        $v = 'N1T';
    } elseif (stripos($a, "; A100 ") !== false) {
        $f = "OPPO";
        $v = 'A100';
    } elseif (stripos($a, "; LT18i") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; LT18i
        $f = "索尼-爱立信";
        $v = "LT18i";
    } elseif (stripos($a, "; W20 ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; LT18i
        $f = "索尼-爱立信";
        $v = "W20";
    } elseif (stripos($a, "; C6603") !== false || stripos($a, "; C5503") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.2.2; zh-CN; C6603 Build/10.3.A.0.423) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        $f = "索尼";
        $v = "C" . GetSubStr($a, "; C", " ");
    } elseif (stripos($a, "; ST25i ") !== false) {
        //K-Touch_T619/960211_8510_V0101 Mozilla/5.0 (Linux;
        $f = "索尼";
        $v = "ST25i";
    } elseif (stripos($a, "; LT") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-CN; LT26ii Build/6.1.A.2.45) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        $f = "索尼";
        $v = "LT" . GetSubStr($a, "; LT", " ");
    } elseif (stripos($a, "; Sony") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; SonyL36h Build/10.1.A.1.350) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "索尼";
        $v = GetSubStr($a, "; Sony", " ");
    } elseif (stripos($a, "; L36h ") !== false) {
        $f = "索尼";
        $v = "L36h";
    } elseif (stripos($a, "; L39h ") !== false) {
        $f = "索尼";
        $v = "L39h";
    } elseif (stripos($a, "; S39h") !== false || stripos($a, "; S36h") !== false) {
        $f = "索尼";
        $v = "S" . GetSubStr($a, "; S", "h") . "h";
    } elseif (stripos($a, "; MK16i") !== false) {
        $f = "索尼";
        $v = "MK16i";
    } elseif (stripos($a, "; ST18i") !== false) {
        $f = "索尼";
        $v = "ST18i";
    } elseif (stripos($a, "C6602") !== false) {
        $f = "索尼";
        $v = "C6602";
    } elseif (stripos($a, ";iOS ") !== false) {
        //IUC(U;iOS 4.3.3;Zh-cn;320*480;)/UCWEB8.2.0.116/41/800
        $f = "apple";
        $v = "iPhone";
    } elseif (stripos($a, ";iOS ") !== false) {
        //IUC(U;iOS 4.3.3;Zh-cn;320*480;)/UCWEB8.2.0.116/41/800
        $f = "apple";
        $v = "iPhone";
    } elseif (stripos($a, "; vivo ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.2.1; zh-CN; vivo X1S Build/JOP40D) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        $f = "步步高";
        $v = "vivo " . GetSubStr($a, "; vivo ", " ");
    } elseif (stripos($a, "zh-cn; GN") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; GN180 Build/IMM76D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.3 Mobile Safari/533.1
        $f = "金立";
        $v = "GN " . GetSubStr($a, "zh-cn; GN", " ");
    } elseif (stripos($a, "zh-cn;GiONEE") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn;GiONEE-GN868H/Phone Build/IMM76D) AppleWebKit534.30(KHTML,like Gecko)Version/4.0 Mobile Safari/534.30 Id/FD34645D0CF3A18C9FC4E2C49F11C510
        $f = "金立";
        $v = GetSubStr($a, "zh-cn;GiONEE", "/");
    } elseif (stripos($a, "GiONEE-") !== false) {
        $f = "金立";
        $v = GetSubStr($a, "GiONEE-", "_TD");
    } elseif (stripos($a, "; GiONEE") !== false) {
        $f = "金立";
        $v = GetSubStr($a, "; GiONEE ", " ");
        $v = $v !== '' ? $v : GetSubStr($a, "; GIONEE_", " ");
    } elseif (stripos($a, "; GIO-GiONEE_") !== false) {
        $f = "金立";
        $v = GetSubStr($a, "; GIO-GiONEE_", "/");
    } elseif (stripos($a, "; GN") !== false) {
        //Dalvik/1.6.0 (Linux; U; Android 4.0.4; GN700W Build/IMM76D)
        $f = "金立";
        $v = GetSubStr($a, "; GN", " ");
    } elseif (stripos($a, "; TD500") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.7; zh-CN; TD500 Build/GWK74) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.0.1.275 U3/0.8.0 Mobile Safari/534.31
        $f = "金立";
        $v = "TD500";
    } elseif (stripos($a, "; V182") !== false) {
        $f = "金立";
        $v = "V182";
    } elseif (stripos($a, "; V185") !== false) {
        $f = "金立";
        $v = "V185";
    } elseif (stripos($a, "; E3T") !== false) {
        $f = "金立";
        $v = "E3T";
    } elseif (stripos($a, "; E6 ") !== false || stripos($a, "; E5 ") !== false) {
        $f = "金立";
        $v = "E" . GetSubStr($a, "; E", " ");
    } elseif (stripos($a, "; DAKELE") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.2; zh-CN; DAKELE MC001 Build/V5) AppleWebKit/534.31 (KHTML, like Gecko) UCBrowser/9.1.1.309 U3/0.8.0 Mobile Safari/534.31
        $f = "大可乐";
        $v = "DAKELE " . GetSubStr($a, "; DAKELE ", " ");
    } elseif (stripos($a, "; HOOW-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.2.1; zh-cn; HOOW-G5 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 baiduboxapp/4.2 (Baidu; P1 4.2.1)
        $f = "宏为";
        $v = "HOOW-" . GetSubStr($a, "; HOOW-", " ");
    } elseif (stripos($a, "; IMDEN ") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0; zh-cn; IMDEN A9200+ Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        //Mozilla/5.0 (Linux; U; Android 4.0; zh-cn; IMDEN-A9200 Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 baidubrowser/3.1.6.4 (Baidu; P1 4.0)
        $f = "爱摩登";
        $v = "IMDEN " . GetSubStr($a, "; IMDEN ", " ");
    } elseif (stripos($a, "; IMDEN-") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0; zh-cn; IMDEN A9200+ Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        //Mozilla/5.0 (Linux; U; Android 4.0; zh-cn; IMDEN-A9200 Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 baidubrowser/3.1.6.4 (Baidu; P1 4.0)
        $f = "爱摩登";
        $v = "IMDEN-" . GetSubStr($a, "; IMDEN-", " ");
    } elseif (stripos($a, "T-smart_") !== false) {
        //T-smart_G18_TD/1.0 Linux/2.6.35 Android/2.3.5 Release/7.16.2012 Mozilla/5.0 (Linux; U; Android 2.2) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 V1_AND_SQ_4.2.0_3_YYB_D
        $f = "天迈";
        $v = GetSubStr($a, "T-smart_", "_TD");
    } elseif (stripos($a, "; Bird") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; Bird I700 Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "波导";
        $v = str_replace([" ", "_"], "", GetSubStr($a, "; Bird", " B"));
    } elseif (stripos($a, " T9508") !== false) {
        $f = "波导";
        $v = 'T9508';
    } elseif (stripos($a, "; DOEASY ") !== false) {
        $f = "波导";
        $v = GetSubStr($a, "; DOEASY ", " ");
    } elseif (stripos($a, "PhilipsT539") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; W732 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "飞利浦";
        $v = "T539";
    } elseif (stripos($a, "; W732") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; W732 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "飞利浦";
        $v = "W732";
    } elseif (stripos($a, "zh-cn; T910") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; T910 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 baiduboxapp/4.4 (Baidu; P1 4.0.4)
        $f = "飞利浦";
        $v = "T910";
    } elseif (stripos($a, "; TOOKY") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.6; zh-cn; TOOKY T1982 Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "京崎";
        $v = GetSubStr($a, "; TOOKY ", " ");
    } elseif (stripos($a, "DATANG-") !== false) {
        //DATANG-DATANG-S11/1.0 Linux/2.6.35.7 Android/4.0.3 Release/12.26.2012 Browser/AppleWebKit533.1 (KHTML, like Gecko) Mozilla/5.0 Mobile
        $f = "大唐";
        $v = GetSubStr($a, "DATANG-", "/");
    } elseif (stripos($a, "; HW-W820") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; HW-W820 Build/IMM76I) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.3 Mobile Safari/533.1
        $f = "海尔";
        $v = "HW-W820";
    } elseif (stripos($a, "; Haier_HT-") !== false) {
        //UCWEB/2.0 (Linux; U; Adr 2.3.5; zh-CN; Haier_HT-I600) U2/1.0.0 UCBrowser/9.1.1.309 U2/1.0.0 Mobile
        $f = "海尔";
        $v = GetSubStr($a, "; Haier_HT-", " ");
    } elseif (stripos($a, "Haier_") !== false) {
        //Haier_HT-I600_TD/I600_MocorDroid2.2_W11.41_V1.00 Release/06.26.2012 Mozilla/5.0 (Linux; U; Android 2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "海尔";
        $v = GetSubStr($a, "Haier_", "_TD");
    } elseif (stripos($a, "; Haier ") !== false) {
        $f = "海尔";
        $v = GetSubStr($a, "; Haier ", " ");
    } elseif (stripos($a, "ChangHong") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; ChangHong V7 Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "长虹";
        $v = GetSubStr($a, "ChangHong ", " ");
    } elseif (stripos($a, "; HT7100") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; HT7100 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "大显";
        $v = "HT7100";
    } elseif (stripos($a, "; CU888") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.1.1; zh-cn; CU888 Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "三普";
        $v = "CU888";
    } elseif (stripos($a, "; WPF") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.6; zh-cn; WPF-W10 Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "沃普丰";
        $v = GetSubStr($a, "; WPF-", " ");
    } elseif (stripos($a, "; AMOI") !== false) {
        //Mozilla/5.0 (Linux; U; Android 4.0.4; zh-cn; AMOI N821 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30
        $f = "夏新";
        $v = GetSubStr($a, "; AMOI ", " ");
    } elseif (stripos($a, "HS-") !== false) {
        //HS-T958_TD/1.0 Android/4.1.2 Release/15.03.2013 Browser/AppleWebKit534.30 Profile/MIDP-2.0 Configuration/CLDC-1.1;
        $f = "海信";
        $v = GetSubStr($a, "HS-", "_TD");
        $v = ($v !== '' ? $v : GetSubStr($a, "HS-", " "));
    } elseif (stripos($a, "KONKA") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; KONKA_V923_TD/1.0 Android/2.3.5 Release/3.27.2013 Browser/AppleWebKit533.1 Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "康佳";
        $v = GetSubStr($a, "KONKA_", "_TD");
        $v = ($v !== '' ? $v : GetSubStr($a, "; KONKA ", " "));
    } elseif (stripos($a, "; W990") !== false) {
        $f = "康佳";
        $v = "W990";
    } elseif (stripos($a, "; ETON") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; ETON T800 Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "亿通";
        $v = GetSubStr($a, "; ETON ", " ");
        $v = $v !== '' ? $v : GetSubStr($a, "; ETON_", " ");
    } elseif (stripos($a, "; T710") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; T710 Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "亿通";
        $v = "T710";
    } elseif (stripos($a, "; T730") !== false) {
        //Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; T730 Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1
        $f = "亿通";
        $v = "T730";
    } elseif (stripos($a, "; Aigo D1") !== false) {
        $f = "爱国者";
        $v = "Aigo D1";
    } elseif (stripos($a, "; MUCH") !== false) {
        $f = "摩奇";
        $v = GetSubStr($a, "; MUCH ", " ");
    } elseif (stripos($a, "; EBEN") !== false) {
        $f = "E人E本";
        $v = GetSubStr($a, "; EBEN ", " ");
    } elseif (stripos($a, "; BLW-VE") !== false) {
        $f = "百灵葳朗";
        $v = GetSubStr($a, "; BLW-VE", "/");
    } elseif (stripos($a, "Lovme-") !== false) {
        $f = "爱我";
        $v = GetSubStr($a, "Lovme-", "/");
    } elseif (stripos($a, "; F999") !== false) {
        $f = "知心";
        $v = 'F999';
    } elseif (stripos($a, "; N616") !== false) {
        $f = "西维";
        $v = 'N616';
    } elseif (stripos($a, "LT_S600D") !== false) {
        $f = "蓝天";
        $v = 'S600D';
    } elseif (stripos($a, "; P76v(") !== false) {
        $f = "台电";
        $v = GetSubStr($a, "; P76v(", ")");
    } elseif (stripos($a, "; KPT ") !== false) {
        $f = "港利通";
        $v = GetSubStr($a, "; KPT ", " ");
    } elseif (stripos($a, "; KliTON-") !== false) {
        $f = "凯利通";
        $v = GetSubStr($a, "; KliTON-", " ");
    } elseif (stripos($a, "S800_ares4") !== false) {
        $f = "奥洛斯";
        $v = 'S800_ares4';
    } elseif (stripos($a, "; TCL ") !== false) {
        $f = "TCL";
        $v = GetSubStr($a, "; TCL ", " ");
    } elseif (stripos($a, "; TCL_") !== false) {
        $f = "TCL";
        $v = GetSubStr($a, "; TCL_", " ");
    } elseif (stripos($a, "HOSIN") !== false) {
        $f = "欧新";
        $v = GetSubStr($a, "HOSIN", " ");
        $v = $v !== '' ? $v : GetSubStr($a, "HOSIN ", "/");
    } elseif (stripos($a, "; NX40X") !== false) {
        $f = "努比亚";
        $v = 'NX40X';
    } elseif (stripos($a, "; NX503A ") !== false) {
        $f = "努比亚";
        $v = 'NX503A';
    } elseif (stripos($a, "; UOOGOU") !== false) {
        $f = "优购";
        $v = GetSubStr($a, "; UOOGOU ", " ");
    } elseif (stripos($a, "; neken ") !== false) {
        $f = "尼凯恩";
        $v = GetSubStr($a, "; neken  ", " ");
        $v = $v !== '' ? $v : GetSubStr($a, "; neken ", ")");
    } elseif (stripos($a, "; mofoto ") !== false) {
        $f = "美富通";
        $v = GetSubStr($a, "; mofoto  ", " ");
    } elseif (stripos($a, "L666") !== false) {
        $f = "美富通";
        $v = "L666";
    } elseif (stripos($a, "; MOFOTO-") !== false) {
        $f = "美富通";
        $v = GetSubStr($a, "; MOFOTO-", " ");
    } elseif (stripos($a, "; 3GNET ") !== false) {
        $f = "3GNET";
        $v = GetSubStr($a, "; 3GNET ", " ");
    } elseif (stripos($a, "; V813 ") !== false || stripos($a, "; V972 ") !== false || stripos($a, "; V975s ") !== false) {
        $f = "昂达";
        $v = "V";
    } elseif (stripos($a, "ONDA") !== false) {
        $f = "昂达";
        $v = "MID";
    } elseif (stripos($a, "; BF_G12 ") !== false) {
        $f = "贝尔丰";
        $v = "BF_G12";
    } elseif (stripos($a, "; BF9500") !== false) {
        $f = "贝尔丰";
        $v = "BF9500";
    } elseif (stripos($a, "; epade_S90") !== false) {
        $f = "易派";
        $v = "S90";
    } elseif (stripos($a, "; epade ") !== false) {
        $f = "易派";
        $v = GetSubStr($a, "; epade ", " ");
    } elseif (stripos($a, "; EBEST W50L") !== false) {
        $f = "E派";
        $v = "W50L";
    } elseif (stripos($a, "ALCATEL") !== false) {
        $f = "ALCATEL";
        $v = GetSubStr($a, ";ALCATEL ", ";");
    } elseif (stripos($a, "ALCATEL") !== false) {
        $f = "ALCATEL";
        $v = GetSubStr($a, ";ALCATEL ", ";");
    } elseif (stripos($a, "; YUYI-") !== false) {
        $f = "ALCATEL";
        $v = GetSubStr($a, "; YUYI-", " ");
    } elseif (stripos($a, "; MORAL_") !== false) {
        $f = "摩能";
        $v = "N01";
    } elseif (stripos($a, "ZP500") !== false) {
        $f = "卓普";
        $v = "ZP500";
    } elseif (stripos($a, "BlackBerry ") !== false) {
        $f = "BlackBerry";
        $v = GetSubStr($a, "BlackBerry ", ";");
    } elseif (stripos($a, "; PadFone") !== false) {
        $f = "华硕";
        $v = "PadFone";
    } elseif (stripos($a, "ASUS Transformer Pad ") !== false) {
        $f = "华硕";
        $v = GetSubStr($a, "ASUS Transformer Pad ", " ");
    } elseif (stripos($a, "; K01F") !== false) {
        $f = "华硕";
        $v = "pad K01F";
    } elseif (stripos($a, "; DOOV") !== false) {
        $f = "朵唯";
        $v = "DOOV";
    } elseif (stripos($a, "; EBEST ") !== false) {
        $f = "尼彩";
        $v = GetSubStr($a, "; EBEST ", " ");
    } elseif (stripos($a, "; MK150") !== false) {
        $f = "美图";
        $v = "MK150";
    } elseif (stripos($a, "MALATA ") !== false) {
        $f = "万利达";
        $v = GetSubStr($a, "MALATA ", " ");
    } elseif (stripos($a, "MASTONE") !== false) {
        $f = "万事通";
        $v = "G3";
    } elseif (stripos($a, "Venue 7") !== false) {
        $f = "戴尔";
        $v = "Venue 7";
    } elseif (stripos($a, "Dell V04B") !== false) {
        $f = "戴尔";
        $v = "V04B";
    } elseif (stripos($a, "; Bambook ") !== false) {
        $f = "盛大";
        $v = GetSubStr($a, "; Bambook ", " ");
    } elseif (stripos($a, "; SAST ") !== false) {
        $f = "先科";
        $v = GetSubStr($a, "; SAST ", " ");
    } elseif (stripos($a, "; Z1208") !== false) {
        $f = "知己";
        $v = "Z1208";
    } elseif (stripos($a, "Bestsonny_") !== false) {
        $f = "至尊宝";
        $v = GetSubStr($a, "Bestsonny_", "_TD");
    } elseif (stripos($a, "; T868 ") !== false) {
        $f = "至尊宝";
        $v = "T868";
    } elseif (stripos($a, "; SOP-") !== false) {
        $f = "兴华宝";
        $v = GetSubStr($a, "; SOP-", " ");
    } elseif (stripos($a, "; COLMEI ") !== false) {
        $f = "威酷";
        $v = GetSubStr($a, "; COLMEI ", " ");
    } elseif (stripos($a, "; A106") !== false) {
        $f = "酷比";
        $v = "A106";
    } elseif (stripos($a, "; deovo ") !== false) {
        $f = "迪为";
        $v = GetSubStr($a, "; deovo ", " ");
    } elseif (stripos($a, "Royalstar_") !== false) {
        $f = "荣事达";
        $v = GetSubStr($a, "Royalstar_", "_TD");
    } elseif (stripos($a, "; KORIDY ") !== false) {
        $f = "快易典";
        $v = GetSubStr($a, "; KORIDY ", " ");
    } elseif (stripos($a, "; VOTO ") !== false) {
        $f = "VOTO";
        $v = GetSubStr($a, "; VOTO ", " ");
    } elseif (stripos($a, "; BOWAY") !== false) {
        $f = "邦华";
        $v = "BOWAY";
    } elseif (stripos($a, "; CONOR ") !== false) {
        $f = "酷诺";
        $v = GetSubStr($a, "; CONOR ", " ");
    } elseif (stripos($a, "; IUSAI ") !== false) {
        $f = "优赛";
        $v = GetSubStr($a, "; IUSAI ", " ");
    } elseif (stripos($a, "; U51GT-") !== false) {
        $f = "酷比魔方";
        $v = GetSubStr($a, "; U51GT-", " ");
    } elseif (stripos($a, "BB10;") !== false) {
        $f = "黑莓";
        $v = "BB10";
    } elseif (stripos($a, "; OPSSON ") !== false) {
        $f = "欧博信";
        $v = GetSubStr($a, "; OPSSON ", " ");
    } elseif (stripos($a, "; Colorfly ") !== false) {
        $f = "七彩虹";
        $v = GetSubStr($a, "; Colorfly ", " ");
    } elseif (stripos($a, "; Intki") !== false) {
        $f = "英特奇";
        $v = "E";
    } elseif (stripos($a, "; AT200 ") !== false) {
        $f = "东芝";
        $v = "AT200";
    } elseif (stripos($a, "; TSC ") !== false) {
        $f = "星语";
        $v = GetSubStr($a, "; TSC ", " ");
    } elseif (stripos($a, "; T9688 ") !== false) {
        $f = "天时达";
        $v = "T9688";
    } elseif (stripos($a, "; SH631W") !== false) {
        $f = "夏普";
        $v = "SH631W";
    } elseif (stripos($a, "; LM-X1+") !== false) {
        $f = "Huaqin";
        $v = "LM-X1";
    } else {
        if (!is_dir("/tmp/logs/phone")) {
            mkdir("/tmp/logs/phone", 0777);
        }
        /*@file_put_contents("/tmp/logs/phone/phone_".md5($a),$a);
        @file_put_contents("/tmp/logs/phone-".date("Ymd",time()).".log",date("Ymd H:i:s",time())."  >>>>>>>START>>>>>>>\n".$a."\n<<<<<<<<END######\n",8);
        @file_put_contents("/tmp/logs/test_phone.log",$a); //用于调试手机用*/
        //echo $a;
    }
    $v = trim(str_replace(["+", ")", "-", " "], "", $v));
    if ($v !== '' && trim($f) !== '') {
        $DevName = trim($f) . '-' . $v;
    } else {
        $DevName = trim($f) . $v;
    }
    return ["DevName" => $DevName, "ComputerName" => $v, "OSName" => trim(trim($o) !== "" ? $o : $v)];
}

/**
 * 获取注册设备数量是否够，1：表示够，0：表示不够
 * @throws Exception
 */
function getDevNumIn()
{
    return SystemServiceProvider::getDevNumIn();
}