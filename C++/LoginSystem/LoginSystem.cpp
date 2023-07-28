#include <windows.h>
#include <iostream>
#include <tchar.h>
#include <random>
#include <sstream>
 #include <WinInet.h>
#pragma comment(lib, "wininet.lib")

using namespace std;

int ExpiryDays;
string Anahtar, HwidAdresi, HWIDStr;

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

int main() {
    cout << "License: ";
    cin >> Anahtar;
    string url = "http://(DOMAIN ADRESINIZ)/licenseapi.php?license=" + Anahtar + "&hwid=" + HwidAdresi + "&expiryend=";
    HINTERNET hInternet = InternetOpenA("WebReader", INTERNET_OPEN_TYPE_DIRECT, NULL, NULL, 0);
    HINTERNET hConnect = InternetOpenUrlA(hInternet, url.c_str(), NULL, 0, INTERNET_FLAG_RELOAD, 0);
    char buffer[1024];
    DWORD bytesRead;
    InternetReadFile(hConnect, buffer, sizeof(buffer), &bytesRead);
    string strResult = buffer;
    size_t hwidPos = strResult.find("HWID: ");
    size_t daysPos = strResult.find("Days: ");
    if (hwidPos != std::string::npos && daysPos != std::string::npos) {
        hwidPos += 6;
        HWIDStr = strResult.substr(hwidPos, daysPos - hwidPos - 1);
        daysPos += 6;
        ExpiryDays = std::stoi(strResult.substr(daysPos));
    }

    HW_PROFILE_INFO hwProfileInfo;
    GetCurrentHwProfile(&hwProfileInfo);
    string hwidWString = hwProfileInfo.szHwProfileGuid;
    string a = hwidWString.substr(1, 8);
    string b = hwidWString.substr(10, 4);
    string c = hwidWString.substr(15, 4);
    HwidAdresi = Karistir(b, c, a);
    
 
    if (HWIDStr == HwidAdresi) {
        cout << "Giris Basarili.\n\n";
        std::cout << "Kalan Lisans: " << ExpiryDays << " Gun." <<std::endl;
    }
    else if (HWIDStr == "Banned") {
        cout << "Hesabiniz yasaklandi.\n\n";
        Sleep(5000);
        exit(0);
    }
    else {
        cout << "HWID eslesmiyor.\n\n";
        Sleep(5000);
        exit(0);
    }
    system("pause");
}
