#Php read ipa file
IpaParser try to unzip ipa file, then read __Info.plist__ & uncrush __*.png__ to get icon app

    + unzip ipa file
    + read Info.plist, package: `rodneyrehm/plist`
    + uncrushed *.png to get icon app, class `PngUncrushed`
    + writing test, `phpunit`
    
##How to use

    /*#instance of IpaParser
    $parser = new IpaParser("/path/to/file.ipa");
    
    #getBasicInfo() return an array from Info.plist
    #icon saved in ipa-info/app-icon.png
    $info = $parser->getBasicInfo();
    
##Sample result
Info.plist

    array (size=33)
      'UIRequiresFullScreen' => boolean true
      'CFBundleInfoDictionaryVersion' => string '6.0' (length=3)
      'UISupportedInterfaceOrientations~ipad' => 
        array (size=2)
          0 => string 'UIInterfaceOrientationLandscapeLeft' (length=35)
          1 => string 'UIInterfaceOrientationLandscapeRight' (length=36)
      'UIInterfaceOrientation' => 
        array (size=1)
          0 => string 'UIInterfaceOrientationLandscapeLeft' (length=35)
      'DTPlatformVersion' => string '9.3' (length=3)
      'CFBundleName' => string 'aia_wpa' (length=7)
      'DTSDKName' => string 'iphoneos9.3' (length=11)
      'UIViewControllerBasedStatusBarAppearance' => boolean false
      'CFBundleIcons' => 
        array (size=0)
          empty
      'LSRequiresIPhoneOS' => boolean true
      'CFBundleDisplayName' => string 'AIA WPA' (length=7)
      'DTSDKBuild' => string '13E230' (length=6)
      'CFBundleShortVersionString' => string '1.0.1' (length=5)
      'CFBundleSupportedPlatforms' => 
        array (size=1)
          0 => string 'iPhoneOS' (length=8)
app-icon.png

![app-icon](https://raw.githubusercontent.com/hoanganh25991/hoanganh25991.github.io/master/images/app-icon-2016-06-13_105958.png)

##Review Youtube
[php-read-ipa-2016-06-13](https://www.youtube.com/watch?v=20c_61B_hPc)

<iframe width="560" height="315" src="https://www.youtube.com/embed/20c_61B_hPc" frameborder="0" allowfullscreen></iframe>