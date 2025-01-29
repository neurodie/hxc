import argparse
import requests
import os
import threading
from datetime import datetime
from queue import Queue
from bs4 import BeautifulSoup
from colorama import Fore, Style, init

# Inisialisasi colorama
init()

# Konfigurasi CMS
CMS_SIGNATURES = [
    {
        'name': 'WordPress',
        'meta_tag': {'name': 'generator', 'content': 'WordPress'},
        'keywords': ['wp-content', 'wp-includes', 'wp-json'],
        'paths': ['/wp-admin/', '/wp-login.php']
    },
    {
        'name': 'Joomla',
        'meta_tag': {'name': 'generator', 'content': 'Joomla'},
        'keywords': ['joomla', 'Joomla!'],
        'paths': ['/media/system/', '/administrator/']
    },
    {
        'name': 'Drupal',
        'meta_tag': {'name': 'generator', 'content': 'Drupal'},
        'keywords': ['Drupal', 'drupal.js'],
        'paths': ['/sites/default/']
    },
    {
        'name': 'Shopify',
        'keywords': ['shopify', 'cdn.shopify.com'],
        'paths': ['/cdn/shop/']
    }
]

# Setup threading
url_queue = Queue()
print_lock = threading.Lock()
file_lock = threading.Lock()
cms_names = {cms['name'] for cms in CMS_SIGNATURES}

def setup_log_directory():
    """Membuat direktori logs jika belum ada"""
    os.makedirs('logs', exist_ok=True)

def generate_log_filename(cms_name, date):
    """Generate nama file log berdasarkan CMS dan tanggal"""
    if cms_name in cms_names:
        return f"logs/{cms_name.lower()}-{date}.txt"
    else:
        return f"logs/not-detect-{date}.txt"

def save_to_log(url, cms_result, log_date):
    """Menyimpan hasil deteksi ke file log"""
    filename = generate_log_filename(cms_result, log_date)
    
    with file_lock:
        with open(filename, 'a', encoding='utf-8') as f:
            timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            log_entry = f"[{timestamp}] {url} - {cms_result}\n"
            f.write(log_entry)

def detect_cms(url):
    try:
        if not url.startswith(('http://', 'https://')):
            url = f'http://{url}'
            
        response = requests.get(
            url,
            timeout=15,
            allow_redirects=True,
            headers={'User-Agent': 'CMS Detector 3.0'}
        )
        
        soup = BeautifulSoup(response.text, 'html.parser')
        content = response.text.lower()
        
        for cms in CMS_SIGNATURES:
            # Cek meta tag
            if 'meta_tag' in cms:
                meta = cms['meta_tag']
                if soup.find('meta', attrs=meta):
                    return cms['name']
            
            # Cek keyword
            if 'keywords' in cms:
                for keyword in cms['keywords']:
                    if keyword.lower() in content:
                        return cms['name']
            
            # Cek path
            if 'paths' in cms:
                for path in cms['paths']:
                    if path in response.text:
                        return cms['name']
        
        return 'Tidak terdeteksi'
    
    except Exception as e:
        return f'Error: {str(e)}'

def worker(log_date):
    """Thread worker untuk proses deteksi"""
    while not url_queue.empty():
        url = url_queue.get()
        result = detect_cms(url)
        save_to_log(url, result, log_date)
        
        # Tampilkan output dengan warna
        with print_lock:
            if result in cms_names:
                color = Fore.GREEN
            else:
                color = Fore.RED
            print(f"{color}{url.ljust(40)} | {result}{Style.RESET_ALL}")
        
        url_queue.task_done()

def process_file(input_file, thread_count):
    setup_log_directory()
    log_date = datetime.now().strftime('%Y-%m-%d')
    
    with open(input_file, 'r') as file:
        urls = [line.strip() for line in file if line.strip()]
    
    # Masukkan URL ke queue
    for url in urls:
        url_queue.put(url)
    
    print(f"\nMemproses {len(urls)} URL dengan {thread_count} threads...\n")
    
    # Buat dan jalankan thread
    threads = []
    for _ in range(thread_count):
        thread = threading.Thread(target=worker, args=(log_date,))
        thread.start()
        threads.append(thread)
    
    # Tunggu semua thread selesai
    for thread in threads:
        thread.join()
    
    print("\n\nProses selesai. Log file tersimpan di direktori 'logs'")

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Detektor CMS Multithread')
    parser.add_argument('-f', '--file', required=True, help='File input berisi URL')
    parser.add_argument('-t', '--threads', type=int, default=5, 
                       help='Jumlah thread (default: 5)')
    args = parser.parse_args()
    
    process_file(args.file, args.threads)
