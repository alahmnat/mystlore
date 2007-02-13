using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Text;
using System.Windows.Forms;

namespace KIImageFixer
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private void Form1_Load(object sender, EventArgs e)
        {

        }

        private void label1_Click(object sender, EventArgs e)
        {

        }

        private void label2_Click(object sender, EventArgs e)
        {

        }

        private void radioButton1_CheckedChanged(object sender, EventArgs e)
        {

        }

        private void radioButton2_CheckedChanged(object sender, EventArgs e)
        {

        }

        private void button2_Click(object sender, EventArgs e)
        {
            progressBar1.Maximum = openFileDialog1.FileNames.Length;
            label3.Text = "";
            panel2.Show();
            panel1.Hide();

            foreach (string filename in openFileDialog1.FileNames)
            {
                bool isBroken = true;
                //MessageBox.Show(filename);
                if (File.Exists(filename))
                {
                    label3.Text = System.IO.Path.GetFileName(filename) + " (" + progressBar1.Value +
                        " out of " + progressBar1.Maximum + ")";
                    
                    FileStream fileStream = new FileStream(filename,
                        FileMode.Open, FileAccess.Read, FileShare.Read);
                    BinaryReader binaryReader = new BinaryReader(fileStream);
                    byte[] bytes = new byte[fileStream.Length];

                    // read the first three bytes to check if they're correct
                    for (int i = 0; i < 3; i++)
                    {
                        bytes[i] = binaryReader.ReadByte(); //255 216 255
                        if ((bytes[0].GetHashCode() == 255) &&
                            (bytes[1].GetHashCode() == 216) && (bytes[2].GetHashCode() == 255))
                            isBroken = false; // if correct, skip this file
                    }

                    if (isBroken == false)
                        continue;

                    // assuming incorrect for the remainder
                    // read another byte and discard it (this is the fourth and final corrupt byte)
                    binaryReader.ReadByte();
                    
                    for (int i = 0; i < (fileStream.Length - 5); i++)
                    {
                        bytes[i] = binaryReader.ReadByte();
                    }

                    binaryReader.Close();
                    fileStream.Close();

                    string newFilename = "";
                    if (radioButton1.Checked)
                        newFilename = filename;
                    if (radioButton2.Checked)
                        newFilename = System.IO.Path.GetDirectoryName(filename) +
                            "\\" + System.IO.Path.GetFileNameWithoutExtension(filename) +
                            " (corrected)" + System.IO.Path.GetExtension(filename);

                    FileStream newFileStream = new FileStream(newFilename,
                        FileMode.Create, FileAccess.Write, FileShare.Read);
                    BinaryWriter newBinaryWriter = new BinaryWriter(newFileStream);

                    newBinaryWriter.Write(bytes);

                    newBinaryWriter.Close();
                    newFileStream.Close();

//                    MessageBox.Show(newFilename);
                }
                progressBar1.PerformStep();
            }
            panel1.Show();
            panel2.Hide();
        }

        private void button1_Click(object sender, EventArgs e)
        {
            openFileDialog1.InitialDirectory = Environment.GetFolderPath(Environment.SpecialFolder.MyDocuments);
            if (openFileDialog1.ShowDialog() == DialogResult.OK)
            {
                string fileNames = "";
                foreach (string s in openFileDialog1.FileNames)
                {
                    fileNames += s;
                }
//                MessageBox.Show(fileNames);
            }
        }

        private void openFileDialog1_FileOk(object sender, CancelEventArgs e)
        {

        }

        private void panel1_Paint(object sender, PaintEventArgs e)
        {

        }

        private void textBox1_TextChanged(object sender, EventArgs e)
        {

        }
    }
}