#include <windows.h>
#include <iostream>
#include <tchar.h>
#include <random>
#include <sstream>
 #include <WinInet.h>
#pragma comment(lib, "wininet.lib")
#include "Auth.h"

using namespace std;

int ExpiryDays;
string Anahtar, HwidAdresi, HWIDStr;

 
int main() {
    cout << "License: ";
    cin >> Anahtar;
    HwidAdresi = HwidVer();
    string url = _xor_("http://5.180.106.192/licenseapi.php?license=").c_str() + BeniSifrele(Anahtar) + _xor_("&hwid=").c_str() + BeniSifrele(HwidAdresi) + _xor_("&expiryend=").c_str();
    HINTERNET hInternet = InternetOpenA(_xor_("WebReader").c_str(), INTERNET_OPEN_TYPE_DIRECT, NULL, NULL, 0);
    HINTERNET hConnect = InternetOpenUrlA(hInternet, url.c_str(), NULL, 0, INTERNET_FLAG_RELOAD, 0);
    char buffer[4096];
    DWORD bytesRead;
    InternetReadFile(hConnect, buffer, sizeof(buffer), &bytesRead);
    string strResult = BeniCoz(buffer).c_str();
    size_t hwidPos = strResult.find(_xor_("HWID: ").c_str());
    size_t daysPos = strResult.find(_xor_("Days: ").c_str());
    if (hwidPos != std::string::npos && daysPos != std::string::npos) {
        hwidPos += 6;
        HWIDStr = strResult.substr(hwidPos, daysPos - hwidPos - 1);
        daysPos += 6;
        ExpiryDays = std::stoi(strResult.substr(daysPos));
    }

    if (strResult.find(_xor_("Invalid license").c_str()) != std::string::npos) {
        cout << _xor_("Lisans bulunamadi.\n\n").c_str();
        Sleep(5000);
        exit(0);
    }
    else if (HWIDStr == HwidAdresi) {
        cout << _xor_("Giris Basarili.\n\n").c_str();
        std::cout << _xor_("Kalan Lisans: ").c_str() << ExpiryDays << _xor_(" Gun.\n\n").c_str() <<std::endl;
    }
    else if (strResult.find(_xor_("expiryed").c_str()) != std::string::npos) {
        cout << _xor_("Lisans sureniz bitti.\n\n").c_str();
        Sleep(5000);
        exit(0);
    }
    else if (HWIDStr == _xor_("Banned").c_str()) {
        cout << _xor_("Hesabiniz yasaklandi.\n\n").c_str();
        Sleep(5000);
        exit(0);
    }
    else {
        cout << _xor_("HWID eslesmiyor.\n\n").c_str();
        Sleep(5000);
        exit(0);
    }
    system(_xor_("pause").c_str());
}
