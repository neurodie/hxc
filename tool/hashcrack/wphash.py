import hashlib
import base64
import time
import sys
from passlib.hash import phpass
from tqdm import tqdm

def check_wordpress_hash(password, hash_wp):
    return phpass.verify(password, hash_wp)

def brute_force_wp(hash_wp, wordlist_file):
    try:
        with open(wordlist_file, "r", encoding="utf-8") as f:
            words = [line.strip() for line in f]
        
        with tqdm(total=len(words), desc="[+] Bruteforcing...", unit="passwords", colour="green", ascii="--=>") as pbar:
            for password in words:
                pbar.set_description(f"[+] Mencoba: {password}")
                pbar.update(1)
                
                if check_wordpress_hash(password, hash_wp):
                    print(f"\n[+] Password ditemukan: {password}")
                    return password
        print("\n[-] Password tidak ditemukan dalam wordlist.")
    except FileNotFoundError:
        print("[!] File wordlist tidak ditemukan.")
    return None

if __name__ == "__main__":
    hash_wp = input("- masukkan hash WordPress: ")
    wordlist_file = input("- masukkan path wordlist: ")

    print("\n[+] Memulai brute force attack...")
    start_time = time.time()
    result = brute_force_wp(hash_wp, wordlist_file)
    end_time = time.time()

    if result:
        print(f"[+] Proses selesai dalam {end_time - start_time:.2f} detik.")
    else:
        print("[!] Password tidak ditemukan.")
