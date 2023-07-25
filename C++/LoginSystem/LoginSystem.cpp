#include <tchar.h>
#include <random>
#include <sstream>
#include <iostream>
#pragma comment(lib, "urlmon.lib")
#include <urlmon.h>

using namespace std;

int ExpiryDays;
string Anahtar, HwidAdresi;
string HWIDStr;

template< typename ... Args >
std::string Karistir(Args const& ... args)
{
    std::ostringstream stream;
    using List = int[];
    (void)List {
        0, ((void)(stream << args), 0) ...
    };
    return stream.str();
}
void HwidVer() {
    HW_PROFILE_INFO hwProfileInfo;
    GetCurrentHwProfile(&hwProfileInfo);
    string hwidWString = hwProfileInfo.szHwProfileGuid;
    string a = hwidWString.substr(1, 8);
    string b = hwidWString.substr(10, 4);
    string c = hwidWString.substr(15, 4);
    string karismis = Karistir(b, c, a);
    HwidAdresi = karismis;
}

int main() {
    HwidVer();
    cout << "License: ";
    cin >> Anahtar;
    string url = "http://(DOMAIN ADRESINIZ)/licenseapi.php?license=" + Anahtar + "&hwid=" + HwidAdresi + "&expiryend=";
    IStream* stream;
    URLOpenBlockingStreamA(0, url.c_str(), &stream, 0, 0);
    char buffer[100];
    unsigned long bytesRead;
    stringstream ss;
    stream->Read(buffer, 100, &bytesRead);
    while (bytesRead > 0U)
    {
        ss.write(buffer, (long long)bytesRead);
        stream->Read(buffer, 100, &bytesRead);
    }
    string strResult = ss.str();
 
    size_t hwidPos = strResult.find("HWID: ");
    size_t daysPos = strResult.find("Days: ");

    if (hwidPos != std::string::npos && daysPos != std::string::npos) {
        hwidPos += 6;
        HWIDStr = strResult.substr(hwidPos, daysPos - hwidPos - 1);
        daysPos += 6;
        ExpiryDays = std::stoi(strResult.substr(daysPos));
    }
   
    if (HWIDStr == "Banned") {
        cout << "Hesabiniz yasaklandi.\n\n";
        Sleep(5000);
        exit(0);
    }
    if (HWIDStr == HwidAdresi) {
        cout << "Giris Basarili.\n\n";


        std::cout << "Kalan Lisans: " << ExpiryDays << " Gun." <<std::endl;

    }
    else {
        cout << "HWID eslesmiyor.\n\n";
        Sleep(5000);
        exit(0);
    }
    system("pause");
}