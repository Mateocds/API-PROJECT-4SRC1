package main

import (
	"encoding/json"
	"fmt"
	"io"
	"net"
	"net/http"
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

type HostInfo struct {
	Status    string `json:"status"`
	Hostname  string `json:"hostname"`
	OS        string `json:"os"`
	CheckedAt string `json:"checked_at"`
}

type CpuInfo struct {
	UsagePercent  int    `json:"total_usage_percent"`
	LogicalCores  int    `json:"logical_cores"`
	PhysicalCores int    `json:"physical_cores"`
	CheckedAt     string `json:"checked_at"`
}

type MemoryInfo struct {
	TotalGB     float64 `json:"total_gb"`
	UsedGB      float64 `json:"used_gb"`
	UsedPercent float64 `json:"used_percent"`
	CheckedAt   string  `json:"checked_at"`
}

type DiskInfo struct {
	TotalGB     float64 `json:"total_gb"`
	UsedGB      float64 `json:"used_gb"`
	FreeGB      float64 `json:"free_gb"`
	UsedPercent float64 `json:"used_percent"`
	CheckedAt   string  `json:"checked_at"`
}

type MonitorData struct {
	Host HostInfo   `json:"host_info"`
	CPU  CpuInfo    `json:"cpu_info"`
	RAM  MemoryInfo `json:"memory_info"`
	Disk DiskInfo   `json:"disk_info"`
}

func CheckAPI(target string) (MonitorData, error) {
	url := "http://" + target + "/api/v1/all"

	var results MonitorData

	resp, err := http.Get(url)
	if err != nil {
		return results, err
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return results, err
	}

	err = json.Unmarshal(body, &results)
	if err != nil {
		return results, err
	}

	return results, nil
}

func printReport(data MonitorData) {

	fmt.Println("--- RAPPORT ---")

	fmt.Printf(`System : 
	OS :      %s 
	Hostname: %s
	Status:   %s
	`, data.Host.OS, data.Host.Status, data.Host.Status)

	fmt.Println("---")

	fmt.Printf(`CPU : 
	Physical cores : %d
	Logical cores :  %d
	Usage Percent :  %d/100
	`, data.CPU.PhysicalCores, data.CPU.LogicalCores, data.CPU.UsagePercent)

	fmt.Println("---")

	fmt.Printf(`RAM : 
	Used : %.2f/%.2f Go
	Usage Percent : %.2f/100
	`, data.RAM.UsedGB, data.RAM.TotalGB, data.RAM.UsedPercent)

	fmt.Println("---")

	fmt.Printf(`Disque : 
	Used : 		   %.2f/%.2f Go
	Free : 		   %.2f Go
	Usage Percent: %.2f/100
	`, data.Disk.UsedGB, data.Disk.TotalGB, data.Disk.FreeGB)

	fmt.Println("---")

	fmt.Printf("Last Check : %s\n", data.Host.CheckedAt)
}

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

		address = net.JoinHostPort(target, "443")

		_, err := net.DialTimeout("udp", address, timeout)

		if err != nil {
			fmt.Println("Error :", target, "is not reachable")
			time.Sleep(3 * time.Second)
		} else {
			quitChan := make(chan bool)

			go func() {
				for {
					var input string
					fmt.Scanln(&input)
					if input == "q" || input == "quit" {
						quitChan <- true
						return
					}
				}
			}()

		MenuLoop:
			for {
				clearScreen()
				fmt.Println(mess)
				fmt.Println("Selectionned System :", target)

				results, err := CheckAPI(target)
				if err != nil {
					fmt.Println("Error while reading API:", err)
				} else {
					printReport(results)
				}

				fmt.Print("Press 'q' to quit : ")

				select {
				case <-quitChan:
					fmt.Println("C'est ciao")
					break MenuLoop
				case <-time.After(5 * time.Second):
					continue
				}
			}
		}
	}
}
