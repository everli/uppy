@php
    echo '<?xml version="1.0" encoding="UTF-8"?>'
@endphp
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd ">
<plist version="1.0">
    <dict>
        <key>items</key>
        <array>
            <dict>
                <key>assets</key>
                <array>
                    <dict>
                        <key>kind</key>
                        <string>software-package</string>
                        <key>url</key>
                        <string><![CDATA[{!! $fileUrl !!}]]></string>
                    </dict>
                </array>
                <key>metadata</key>
                <dict>
                    <key>bundle-identifier</key>
                    <string>{!! $package ?? 'com.default.identifier' !!}</string>
                    <key>bundle-version</key>
                    <string>{{ $bundleVersion }}</string>
                    <key>kind</key>
                    <string>software</string>
                    <key>title</key>
                    <string>{{ $title }}</string>
                </dict>
            </dict>
        </array>
    </dict>
</plist>
