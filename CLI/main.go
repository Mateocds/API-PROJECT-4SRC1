package main

import (
	"fmt"
	"net"
	"os"
	"os/exec"
	"runtime"
	"time"
)

var mess string = `Welcome to our CLI - Group N°2

This program permets you to observe all states of your system
-------------------------------------------------------------`

var menu string = `---
1. HEALTH state
2. CPU state
3. MEMORY state
4. DISK state
5. ALL
---
6. Change target system
7. Quit`

func clearScreen() {
	var cmd *exec.Cmd
	if runtime.GOOS == "windows" {
		cmd = exec.Command("cls")
	} else {
		cmd = exec.Command("clear")
	}
	cmd.Stdout = os.Stdout
	cmd.Run()
}

func State(_option string, _target string) {
	for {
		clearScreen()
		fmt.Println(mess)
		fmt.Println("Selectionned System :", _target)

		switch _option {
		case "1", "2", "3", "4", "5":
			fmt.Println("Option", _option, "is selectionned")
		default:
			fmt.Println("No display")

		}

		fmt.Print("\nPress 'q' to quit : ")
		var input string
		fmt.Scanln(&input)
		if input == "q" {
			return
		}
	}
}

func main() {
	var target string
	var address string
	timeout := 2 * time.Second

	for {
		target = ""

		clearScreen()
		fmt.Println(mess)
		fmt.Print("Whish system do you want to scan ? (by default : localhost) : ")
		fmt.Scanln(&target)
		if target == "" {
			target = "localhost"
		}
		fmt.Println("System choose :", target)

		address = net.JoinHostPort(target, "80")

		_, err := net.DialTimeout("udp", address, timeout)

		if err != nil {
			fmt.Println("Error :", target, "is not reachable")
			time.Sleep(3 * time.Second)
		} else {
		MenuLoop:
			for {
				clearScreen()
				fmt.Println(mess)
				fmt.Println("Selectionned System :", target)
				fmt.Println("What do you want to scan ?")
				fmt.Println(menu)
				fmt.Print("Select an option : ")

				var option string
				fmt.Scanln(&option)

				switch option {
				case "1", "2", "3", "4", "5":
					State(option, target)
				case "6":
					break MenuLoop
				case "7", "q", "quit":
					fmt.Println("C'est ciao")
					return
				default:
					fmt.Println("Invalid option. Try again")
					time.Sleep(2 * time.Second)
				}
			}
		}
	}
}
