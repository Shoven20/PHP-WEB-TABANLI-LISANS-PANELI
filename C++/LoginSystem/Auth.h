#include <windows.h>
#include <iostream>
#include <sstream>
#include <WinInet.h>
#pragma comment(lib, "wininet.lib")
#include "xor.h"

std::string roatatealphabet(const std::string& input) {
    std::string output = input;
    for (char& c : output)
        if (std::isalpha(c))
            if ((c >= 'A' && c <= 'Z') || (c >= 'a' && c <= 'z')) {
                char base = (std::isupper(c)) ? 'A' : 'a';
                c = ((c - base + 13) % 26) + base;
            }
    return output;
}
const std::string base64_chars = _xor_("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/").c_str();
bool is_base64(unsigned char c) {
    return (isalnum(c) || (c == '+') || (c == '/'));
}
std::string base64_decode(const std::string& encoded) {
    size_t in_len = encoded.size();
    size_t i = 0;
    size_t j = 0;
    int in_ = 0;
    unsigned char char_array_4[4], char_array_3[3];
    std::string decoded;
    while (in_len-- && (encoded[in_] != '=') && is_base64(encoded[in_])) {
        char_array_4[i++] = encoded[in_]; in_++;
        if (i == 4) {
            for (i = 0; i < 4; i++) char_array_4[i] = base64_chars.find(char_array_4[i]);
            char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
            char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
            char_array_3[2] = ((char_array_4[2] & 0x3) << 6) + char_array_4[3];
            for (i = 0; i < 3; i++) decoded += char_array_3[i];
            i = 0;
        }
    }
    if (i > 0) {
        for (j = i; j < 4; j++) char_array_4[j] = 0;
        for (j = 0; j < 4; j++) char_array_4[j] = base64_chars.find(char_array_4[j]);
        char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
        char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
        for (j = 0; j < i - 1; j++)decoded += char_array_3[j];
    }

    return decoded;
}
std::string base64_encode(const std::string& input) {
    std::string encoded;
    size_t i = 0;
    unsigned char char_array_3[3];
    unsigned char char_array_4[4];
    for (const auto& c : input) {
        char_array_3[i++] = c;
        if (i == 3) {
            char_array_4[0] = (char_array_3[0] & 0xfc) >> 2;
            char_array_4[1] = ((char_array_3[0] & 0x03) << 4) + ((char_array_3[1] & 0xf0) >> 4);
            char_array_4[2] = ((char_array_3[1] & 0x0f) << 2) + ((char_array_3[2] & 0xc0) >> 6);
            char_array_4[3] = char_array_3[2] & 0x3f;
            for (size_t j = 0; j < 4; j++) encoded += base64_chars[char_array_4[j]];
            i = 0;
        }
    }
    if (i > 0) {
        for (size_t j = i; j < 3; j++) char_array_3[j] = '\0';
        char_array_4[0] = (char_array_3[0] & 0xfc) >> 2;
        char_array_4[1] = ((char_array_3[0] & 0x03) << 4) + ((char_array_3[1] & 0xf0) >> 4);
        char_array_4[2] = ((char_array_3[1] & 0x0f) << 2) + ((char_array_3[2] & 0xc0) >> 6);
        for (size_t j = 0; j < i + 1; j++) encoded += base64_chars[char_array_4[j]];
        while (i++ < 3) encoded += '=';
    }
    return encoded;
}

std::string HwidVer() {
    DWORD serialNumber;
    GetVolumeInformation("C:\\", NULL, 0, &serialNumber, NULL, NULL, NULL, 0);
    return std::to_string(serialNumber);
}
std::string BeniSifrele(std::string text)
{
    return base64_encode(roatatealphabet(base64_encode(text.c_str())));
}
std::string BeniCoz(std::string text)
{
    return base64_decode(roatatealphabet(base64_decode(text.c_str())));
}
