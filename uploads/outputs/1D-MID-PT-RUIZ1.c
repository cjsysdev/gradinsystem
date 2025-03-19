#include <stdio.h>

#define MAX_STUDENTS 100


struct Student {
    char name[50];
    int age;
};


struct Student students[MAX_STUDENTS];
int studentCount = 0;


void displayMenu();
void addStudent();
void displayStudents();

int main() {
    int choice;

    while (1) { 
        displayMenu();
        printf("Enter your choice: ");
        scanf("%d", &choice);

        switch (choice) {
            case 1:
                addStudent();
                break;
            case 2:
                displayStudents();
                break;
            case 3:
                printf("Exiting the program. Goodbye!\n");
                return 0; 
            default:
                printf("Invalid choice. Please try again.\n");
        }
    }

    return 0;
}


void displayMenu() {
    printf("\n=== Student Management System ===\n");
    printf("1. Add Student\n");
    printf("2. Display Students\n");
    printf("3. Exit\n");
}


void addStudent() {
    if (studentCount < MAX_STUDENTS) {
        printf("\nEnter student name: ");
        scanf(" %[^\n]", students[studentCount].name); 
        printf("Enter student age: ");
        scanf("%d", &students[studentCount].age);
        studentCount++;
        printf("Student added successfully!\n");
    } else {
        printf("Student list is full. Cannot add more students.\n");
    }
}


void displayStudents() {
    if (studentCount == 0) {
        printf("\nNo students in the list.\n");
    } else {
        printf("\n=== Student List ===\n");
        for (int i = 0; i < studentCount; i++) {
            printf("Student %d: Name: %s, Age: %d\n", i + 1, students[i].name, students[i].age);
        }
    }
}